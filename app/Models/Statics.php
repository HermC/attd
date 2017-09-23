<?php

namespace App\Models;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Stoneworld\Wechat\Broadcast;
use Stoneworld\Wechat\Message;
use Stoneworld\Wechat\Messages\NewsItem;
class Statics  extends Model
{
  
	protected $table = 'statics';

	

	//计划任务  每天提醒 上班后三十分钟  推送前一天部门考勤情况
	public  static function pushDayOrWeekReportToLeaderOfDep($week){

		$leaders = Department::getLeaders()->pluck("userid")->all();

		$broadcast = new Broadcast( config("ent.appId"), config("ent.secret"));
		if(!$week){
			$yesterday = Carbon::now()->subDay()->toDateString();
			$title = $yesterday.'考勤概况';
		}else{
			$dt = Carbon::now()->subWeek();
			$yesterday = $dt->month."月第".$dt->weekOfMonth."周";
			$title = $yesterday.'考勤概况';
		}
		
		$newsItem = new NewsItem();
		$newsItem->title = $title;
		$newsItem->description = $title;
		$newsItem->pic_url = 'http://nqi.iselab.cn/img/statics.png';
		$newsItem->url = route( "statics", ["week"=>$week]  );
		
		$message = Message::make('news')->item($newsItem);
		$res = $broadcast->fromAgentId( config('ent.attendant_chat') )->send($message)->to($leaders,'','');
		return $res;
		
	}
	
	
	/**
	 * 生成月出勤统计报告 带单元格排版的那个
	 * @param unknown $dep
	 * @param unknown $start
	 * @param unknown $end
	 */
	public static  function makeAttendantMonthlyStatics($dep,$start,$end){
	
		$month = $start->month;
		$endDayOfMonth = $end->day;
		if($dep==0){
			$attendants = Attendant::where('month',$month)->orderBy("created_at","desc")->get()->groupBy("employee_name");
		}else{
			$attendants = Attendant::where('month',$month)->where("depart_id",$dep)->orderBy("created_at","desc")->get()->groupBy("employee_name");
		}
	
		$dataset = [];
		$i = 0;
		foreach ($attendants as $employee=>$at){
	
			$item = [];
			$item["序号"] = $i;
			$item["部门"] = $at[0]->depart_name;
			$item["姓名"] = $at[0]->employee_name;
			for($i=1 ; $i<=$endDayOfMonth ; $i++){
				 
				//$item[$i."号"] =
			}
	
		}
		dd($attendants);

	}
	
	/***
	 * 概览统计表excel 如果dep为0，输出所有部门通览   如果不为0，生成部门统计数据
	 */
	public static function  overviewExcel($dep,$span){
		$request = app('request');
		//$dep =  $request->input('dep');
		//$span = [$this->data["time1"],$this->data["time2"]];
		 
		list($sheet1Data,$sheet2Data,$sheet3Data) = $this->makeAttendantStatics($dep,$span);
	
		if($dep==0){
			$excelName = $span[0].'-'.$span[1]."所有部门考勤报告";
		}else{
			$department = Department::find($dep);
			$excelName = $span[0].'-'.$span[1].$department->name."部门考勤报告";
		}
		
		Excel::create($excelName, function($excel)use($sheet1Data,$sheet2Data,$sheet3Data,$dep) {
	
			if($dep!=0){
				$excel->sheet('统计情况', function($sheet)use($sheet1Data){
					if(count($sheet1Data)!==0){
						$sheet1Data[0]=[
							"员工姓名"=>$sheet1Data[0]["employee"],
							"所属部门"=>$sheet1Data[0]["deparment"],
							"是否全勤"=>$sheet1Data[0]["isFull"],
							"迟到次数"=>$sheet1Data[0]["lateTimes"],
							"早退次数"=>$sheet1Data[0]["earlyTimes"],
							"缺勤天数"=>$sheet1Data[0]["abcentDays"],
							"外勤次数"=>$sheet1Data[0]["outTimes"],
						];
					}
					$sheet->fromArray($sheet1Data,null, 'A1', true);
				});
	
					// Our second sheet
					$excel->sheet('详细记录', function($sheet)use($sheet2Data) {
	
						if(count($sheet2Data)!==0){
							$sheet2Data[0]=[
							"姓名"=>$sheet2Data[0]["employee_name"],
							"所属部门"=>$sheet2Data[0]["deparment"],
							"日期"=>$sheet2Data[0]["date"],
							"规定签到时间"=>$sheet2Data[0]["ruleSiginTime"],
							"签到时间"=>$sheet2Data[0]["siginTime"],
							"签到状态"=>$sheet2Data[0]["siginState"],
							"签到地点"=>$sheet2Data[0]["siginAddress"],
							"规定签退时间"=>$sheet2Data[0]["ruleSigoutTime"],
							"签退时间"=>$sheet2Data[0]["sigoutTime"],
							"签退状态"=>$sheet2Data[0]["sigoutState"],
							"签退地点"=>$sheet2Data[0]["sigoutAddress"],
							"外勤次数"=>$sheet2Data[0]["outTimes"],
							//"外勤地点1"=>$sheet2Data[0]["outAdress1"],
							//"外勤地点2"=>$sheet2Data[0]["outAdress2"],
							];
						}
						$sheet->fromArray($sheet2Data,null, 'A1', true);
					});
			}else{
				$static = [];
				$alldeparts = Department::all()->keyBy("name");
	
				foreach ($sheet3Data as $dep=>$data){
					$workers = $alldeparts[$dep]->employee()->count();
					$static[]=[
					"部门名称"=>$dep,
					"部门人数"=>$workers,
					"全勤率"=>($workers)?(100*floor($data['fullDays']))/$workers."%":"0%" ,
					"全勤人数"=>intval($data['fullDays']),
					"人均迟到次数"=>($workers)?$data['lateCount']/$workers:0 ,
					"人均早退次数"=>($workers)?$data['earlyCount']/$workers:0 ,
					"人均缺勤天数"=>($workers)?$data['abcentDays']/$workers:0,
					"人均外勤次数"=>($workers)?$data['outCuont']/$workers:0
					];
				}
				$excel->sheet('所有部门统计情况', function($sheet)use($static) {
					$sheet->fromArray($static,null, 'A1', true);
				});
	
					//dd($static);
					// 导出所有部门统计数据
					$departData = collect($sheet1Data)->groupBy("deparment")->all();
					foreach ($departData as $dep=>$data){
						//dump($data);
						$excel->sheet($dep, function($sheet)use($dep,$data) {
	
							if(count($data)!==0){
								$data[0]=[
								"员工姓名"=>$data[0]["employee"],
								"所属部门"=>$data[0]["deparment"],
								"是否全勤"=>$data[0]["isFull"],
								"迟到次数"=>$data[0]["lateTimes"],
								"早退次数"=>$data[0]["earlyTimes"],
								"缺勤天数"=>$data[0]["abcentDays"],
								"外勤次数"=>$data[0]["outTimes"],
								];
							}
							$sheet->fromArray($data,null, 'A1', true);
	
						});
	
					}
					//dd('test');
			}
			 
	
		})->download('xls');
	
		//dump('create excel end');
	}
	
	
	public static  function leaderReport($dep=0,$week=false){
		
		//日报 前一天的综合报告
		if($week){
			$dt = Carbon::now()->subWeek();
			$begin = $dt->startOfWeek()->toDateTimeString();
			$end = $dt->endOfWeek()->toDateTimeString(); //除去周末
		}else{
			$dt = Carbon::now()->subDay();
			$begin = $dt->startOfDay()->toDateTimeString();
			$end = $dt->endOfDay()->toDateTimeString();
		}
		
		list($overview,$detail) = static::makeAttendantStatics($dep,[$begin,$end  ]);

        $os = [
            "normal"=>["count"=>0,"list"=>[] ],
            "late"=>["count"=>0,"list"=>[] ],
            "early"=>["count"=>0,"list"=>[]],
            "out"=>["count"=>0,"list"=>[]],
            "abcent"=>["count"=>0,"list"=>[]]
        ];

        foreach ($overview as $o ){
            $employee = $o["employee"];
            if($o["isFull"]=="是"){
                $os["normal"]["count"]++;
                if(!isset($os["normal"]["list"][$employee])){
                    $os["normal"]["list"][$employee]=1;
                }else{
                    $os["normal"]["list"][$employee]++;
                }

            }
            if( $o["lateTimes"]>0 ){
                $os["late"]["count"] += $o["lateTimes"];
                if(!isset($os["late"]["list"][$employee])){
                    $os["late"]["list"][$employee]=1;
                }else{
                    $os["late"]["list"][$employee]++;
                }
            }

            if( $o["earlyTimes"]>0 ){
                $os["early"]["count"] += $o["earlyTimes"];
                if(!isset($os["early"]["list"][$employee])){
                    $os["early"]["list"][$employee]=1;
                }else{
                    $os["early"]["list"][$employee]++;
                }
            }
            if( $o["outTimes"]>0 ){
                $os["out"]["count"] += $o["outTimes"];
                if(!isset($os["out"]["list"][$employee])){
                    $os["out"]["list"][$employee]=1;
                }else{
                    $os["out"]["list"][$employee]++;
                }
            }
            if( $o["abcentDays"]>0 ){
                $os["abcent"]["count"] += $o["outTimes"];
                if(!isset($os["abcent"]["list"][$employee])){
                    $os["abcent"]["list"][$employee]=1;
                }else{
                    $os["abcent"]["list"][$employee]++;
                }
            }
        }

		return $os;
	}


	/**
	 * 获取详细统计报告
	 * @param number $dep
	 * @param unknown $time
	 * @return multitype:Ambigous <number, multitype:multitype:number  > multitype:
	 */
	public static function makeAttendantStatics($dep=0,$time){
		if($dep==0){
			$attendants = AttendantLog::whereBetween('created_at',$time)->with('employee')->with('department')->with('rule')->orderBy("created_at")->get()->groupBy("employee_id");
		}else{
			$attendants = AttendantLog::whereBetween('created_at',$time)->where("depart_id",$dep)->with('employee')->with('department')->with('rule')->orderBy("created_at")->get()->groupBy("employee_id");
		}
		$sheet1Data = [] ;
		$sheet2Data = [] ;
		$sheet3Data = [] ;//综合情况
		$sheet4Data = [];//请假情况
		//$sheet1Data[]=["employee","deparment","微信号","isFull","lateTimes","earlyTimes","abcentDays","outTimes"];
		//dd($attendants);
		foreach($attendants as $employeeId=>$data1){
		
			if( !isset( $sheet1Data[$employeeId] ) ){
				$sheet1Data[$employeeId]=[ "employee"=>"" , "deparment"=>"" , "isFull"=>"是", "lateTimes"=>0 , "earlyTimes"=>0 ,"abcentDays"=> 0 , "outTimes"=>0 ]; //初始化统计数据
			}
			
			$dateDetail = $data1->GroupBy("log_time");
			
			foreach($dateDetail as $date=>$logs){
				 
				//先判断类型
				//每个人 多天报告
				//rule有可能为空 全天缺勤的情况下
				 
				foreach($logs as $log){
		
					if( !isset( $sheet3Data[$log->depart_name] ) ){
						$sheet3Data[$log->depart_name]=[
							"fullDays"=>0 ,
							"lateCount"=>0,
							"earlyCount"=>0 ,
							"abcentDays"=>0 ,
							"outCuont"=>0
						];
					}
					
					$rule = $log->rule;
					$attendant = $log->attendant;

					if( !isset( $sheet2Data[$employeeId][$date] ) ){
						list($outs,$counts,$vocations,$holidays) = $attendant->getAttendantResult();
						
						$sheet1Data[$employeeId]["isFull"]="否";
						$sheet1Data[$employeeId]["employee"]=$log->employee_name;
						$sheet1Data[$employeeId]["deparment"]=$log->depart_name;
						$sheet2Data[$employeeId][$date] = [
						"employee_name"=>$log->employee_name,
						"deparment"=>$log->depart_name ,
						"date"=>$date,
						"ruleSiginTime"=>($rule)?$rule->sigin_time:"",//外勤 缺勤 签到都没有时间
						"siginTime"=>"",
						"siginState"=>"",
						"siginAddress"=>"",
						"ruleSigoutTime"=>($rule)?$rule->sigout_time:"",
						"sigoutTime"=>"",
						"sigoutState"=>"",
						"sigoutAddress"=>"",
						"outTimes"=>0, //一天最多2次
						//"outAdress1"=>"",
						//"outAdress2"=>""
                        ]; //详细表数据
						
						if( $counts["normal"]==1 ){
							$sheet1Data[$employeeId]["isFull"]= "是";
							$sheet3Data[$log->depart_name]["fullDays"] += $log->amount;
							$sheet2Data[$employeeId][$date]["siginState"] = "正常";
							$sheet2Data[$employeeId][$date]["sigoutState"] = "正常";
						}else{
							if( $counts["late"]>0 ){
								$sheet2Data[$employeeId][$date]["siginState"] = "迟到";
							}
							if( $counts["early"]>0 ){
								$sheet2Data[$employeeId][$date]["sigoutState"] = "早退";
							}
							if( $counts["abcent"]>0 ){
								if($counts["sigin_abcent"]){
									$sheet2Data[$employeeId][$date]["siginState"] = "缺勤";
								}
								if($counts["sigout_abcent"]){
									$sheet2Data[$employeeId][$date]["sigoutState"] = "缺勤";
								}
							}
						}
						$sheet1Data[$employeeId]["lateTimes"] += $counts["late"];
						$sheet1Data[$employeeId]["earlyTimes"] += $counts["early"];
						$sheet1Data[$employeeId]["abcentDays"] += $counts["abcent"];
						$sheet1Data[$employeeId]["outTimes"] += $outs*0.5;
							
						$sheet3Data[$log->depart_name]["lateCount"] += $counts["late"];
						$sheet3Data[$log->depart_name]["earlyCount"] += $counts["early"];
						$sheet3Data[$log->depart_name]["abcentDays"] += $counts["abcent"];
						$sheet3Data[$log->depart_name]["outCuont"] += $outs*0.5;

					}
					switch ($log->type){
						case AttendantLog::TYPE_SIGIN:
							$sheet2Data[$employeeId][$date]["siginTime"]=$log->created_at->format("H:i");
							$sheet2Data[$employeeId][$date]["siginAddress"]=!empty($log->target_address)?$log->target_address:$log->location;
							$rule =$log->rule;
							if(isset($rule)){
								$sheet2Data[$employeeId][$date]["ruleSiginTime"]=$rule->sigin_time;
							}
						break;
						case AttendantLog::TYPE_SIGOUT:
							$sheet2Data[$employeeId][$date]["sigoutTime"]=$log->created_at->format("H:i");
							$sheet2Data[$employeeId][$date]["sigoutAddress"]=!empty($log->target_address)?$log->target_address:$log->location;
							if(isset($rule)){
								$sheet2Data[$employeeId][$date]["ruleSigoutTime"]=$rule->sigout_time;
							}
							break;
						case AttendantLog::TYPE_OUTDOOR:
							if( $log->created_at->hour <= 12 ){
								//表明未签到
								$sheet2Data[$employeeId][$date]["siginState"]="外勤";
								$sheet2Data[$employeeId][$date]["siginTime"]=$log->created_at->format("H:i");
								$sheet2Data[$employeeId][$date]["siginAddress"]=$log->location;
								//$sheet2Data[$employeeId][$date]["outAdress1"]= $log->location;
							}else{
								$sheet2Data[$employeeId][$date]["sigoutState"]="外勤";
								$sheet2Data[$employeeId][$date]["sigoutTime"]=$log->created_at->format("H:i");
								$sheet2Data[$employeeId][$date]["sigoutAddress"]=$log->location;
								//$sheet2Data[$employeeId][$date]["outAdress2"]=$log->location;
							}
                            $sheet2Data[$employeeId][$date]["outTimes"] += 0.5;
						break;
						
					}
				
				}
			}
		}
		 
		$sheet2Data = collect($sheet2Data)->flatten(1)->all();
		 
		/* if($dep==0){
		 $sheet3Data = collect($sheet1Data)->values()->groupBy("deparment");
		dd($sheet3Data);
		} */
		//dd($sheet1Data);
		 
		$sheet1Data = collect($sheet1Data)->values()->all();
		 
		return [$sheet1Data,$sheet2Data,$sheet3Data];
	}

}
