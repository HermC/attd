<?php

namespace App\Models;

use Carbon\Carbon;
class Attendant  extends Model
{
  
	protected $table = 'attendance';

	protected $appends = ['vtime','vstate','vweek'];
	public function getVtimeAttribute(){
		return $this->created_at->format('Y-m-d');
	}
    public function getVweekAttribute(){
        if($this->week==0){
            return "星期日";
        }else{
            return "星期".$this->week;
        }
    }
	public function getVstateAttribute(){
         // $this->result = serialize([1,["normal"=>0,"abcent"=>0,"late"=>0,"early"=>0,"sigout_abcent"=>false,"sigin_abcent"=>false],2,3]);
        list($outs,$counts,$vocations,$holidays) = $this->getAttendantResult();
		 $desc = "";
		 /* if($counts["normal"]!=0){
		 	$desc .= " 正常 : {$counts["normal"]}天 ";
		 } */
		 if($outs>0){
		 	$desc .= "外勤 : {$outs}次  ";
		 }
		 if($vocations>0){
		 	$desc .= " 事假 : {$vocations}天  ";
		 }
		 if($holidays>0){
		 	$desc .= "  休假 : {$holidays}天";
		 }
		 if($counts["late"]!=0){
		 	$desc .= " 迟到 : {$counts["late"]}天 ";
		 } 
		 if($counts["early"]!=0){
		 	$desc .= " 早退 : {$counts["early"]}天 ";
		 }
		 if($counts["abcent"]!=0){
		 	$desc .= " 缺勤 : {$counts["abcent"]}天 ";
		 }
		
		 return $desc;
		/* switch($this->state){
			case 1:
				if($this->rule_id==0){
					return "休假";
				}else{
					return "正常";
				}
			case 2:
				$d = 1-($this->total);
				return "缺勤".$d."天";
			case 3:
				return "休息";
		} */
	}
	//获取当天考勤状态统计  根据考勤日志计算
	public  function getAttendantResult($realtime=false){
        if(!$realtime && !empty($this->result)){
            $history = unserialize($this->result);
            return $history;
        }
		$res = ["normal"=>0,"abcent"=>0,"late"=>0,"early"=>0,"sigout_abcent"=>false,"sigin_abcent"=>false];
		$outs = 0;
		$vocation=0;
		$holiday=0;
		$logs =$this->logs;
		if(!$logs || count($logs)==0){
			//@2-25
			$res["abcent"] = 1;
			$res["noon_abcent"]=true;
			$res["morning_abcent"]=true;
		}
		$morningLimit=Carbon::createFromFormat("H:i", "12:00"); //后期要从rule中获取
		$noonLimit=Carbon::createFromFormat("H:i", "13:00"); 


		if(count($logs)>0 && $logs[0]->type==AttendantLog::TYPE_SIGIN){
			
			if( $logs[0]->state == AttendantLog::STATE_ABCENT  ){
				//如果第一条就是缺勤，肯定是系统生成的，那么整个都是缺勤
				if( !isset( $logs[1] ) || $logs[1]->state==AttendantLog::STATE_ABCENT  ){
					$res["abcent"] = 1;
					$res["sigout_abcent"]=true;
					$res["sigin_abcent"]=true;
				}else{
					if($logs[1]->type==AttendantLog::TYPE_OUTDOOR || $logs[1]->type==AttendantLog::TYPE_HOLIDAY || $logs[1]->type==AttendantLog::TYPE_VOCATION){
						//
						$res["abcent"] = 0.5;
						$res["sigin_abcent"]=true;
						$res["sigout_abcent"]= false;
						$res["normal"] = 0.5;
						if($logs[1]->type==AttendantLog::TYPE_OUTDOOR){
							$outs++;
						}
						if($logs[1]->type==AttendantLog::TYPE_HOLIDAY){
							$holiday +=0.5;
						}
						if($logs[1]->type==AttendantLog::TYPE_VOCATION){
							$vocation +=0.5;
						}
					}
				}
				
			}
			if( $logs[0]->state == AttendantLog::STATE_NOMAL  ){
				//9点之前
				if( !isset( $logs[1] ) || $logs[1]->state==AttendantLog::STATE_ABCENT  ){
					
					//如果不存在签退记录 缺勤 @2 @2->2
					$res["normal"] = 0.5;
					$res["abcent"] = 0.5;
					$res["sigin_abcent"]=false;
					$res["sigout_abcent"]= true;

				}else{
					if($logs[1]->type==AttendantLog::TYPE_OUTDOOR || $logs[1]->type==AttendantLog::TYPE_HOLIDAY || $logs[1]->type==AttendantLog::TYPE_VOCATION){
						//@2->3
						$res["normal"] = 1;
						if($logs[1]->type==AttendantLog::TYPE_OUTDOOR){
							$outs++;
						}
						if($logs[1]->type==AttendantLog::TYPE_HOLIDAY){
							$holiday +=0.5;
						}
						if($logs[1]->type==AttendantLog::TYPE_VOCATION){
							$vocation +=0.5;
						}
					}else{
						//如果存在签退记录
						if( $logs[1]->state == AttendantLog::STATE_NOMAL  ){
							//全勤 @1
							$res["normal"] = 1;
						}
						if( $logs[1]->state == AttendantLog::STATE_EARLY  ){
							//$earlyHour = $logs[1]->created_at->hour;
							if($logs[1]->created_at->lt($morningLimit) ){
								//9点 到 12点 @2
								$res["early"] = 0.5;
								$res["abcent"] = 0.5;
								$res["sigin_abcent"]=true;
								$res["sigout_abcent"]=  false;
							}
							if($logs[1]->created_at->gt($morningLimit) && $logs[1]->created_at->lt($noonLimit) ){
								//12点 到 13点 @3
								$res["abcent"] = 0.5;
								$res["normal"] = 0.5;
								$res["sigin_abcent"]= false;
								$res["sigout_abcent"]=    true;
							}
							if($logs[1]->created_at->gt($noonLimit) && $logs[1]->state==AttendantLog::STATE_EARLY ){
								//13点 到 17点 @4
								$res["early"] = 0.5;
								$res["normal"] = 0.5;
							}
						}	
					}
				}
			}//state normal
			if( $logs[0]->state == AttendantLog::STATE_LATE   ){
				if( $logs[0]->created_at->lt($morningLimit ) ){
					if( !isset( $logs[1] ) || $logs[1]->state==AttendantLog::STATE_ABCENT  ){
					
						//如果不存在签退记录 缺勤 @2  @2->7
						$res["late"] = 0.5;
						$res["abcent"] = 0.5;
						$res["sigin_abcent"]= false;
						$res["sigout_abcent"]=    true;
					
					}else{
						if($logs[1]->type==AttendantLog::TYPE_OUTDOOR || $logs[1]->type==AttendantLog::TYPE_HOLIDAY || $logs[1]->type==AttendantLog::TYPE_VOCATION){
							//@2->5
							$res["normal"] = 0.5;
							$res["late"] = 0.5;
							if($logs[1]->type==AttendantLog::TYPE_OUTDOOR){
								$outs++;
							}
							if($logs[1]->type==AttendantLog::TYPE_HOLIDAY){
								$holiday +=0.5;
							}
							if($logs[1]->type==AttendantLog::TYPE_VOCATION){
								$vocation +=0.5;
							}
						}else{
								
							if($logs[1]->state==AttendantLog::STATE_ABCENT ){
								// @2->6
								$res["late"] = 0.5;
								$res["abcent"] = 0.5;
								$res["sigin_abcent"]= false;
								$res["sigout_abcent"]=    true;
							}
								
							if($logs[1]->state==AttendantLog::STATE_NOMAL ){
								//12点之前  早退1 @22 @23 @24 @25
								$res["late"] = 0.5;
								$res["normal"] = 0.5;
							}
								
							if( $logs[1]->state == AttendantLog::STATE_EARLY  ){
								//$earlyHour = $logs[1]->created_at->hour;
								if($logs[1]->created_at->lt($morningLimit) ){
									//9点 到 12点 @14
									$res["late"] = 0.5;
									$res["abcent"] = 0.5;
									$res["sigin_abcent"]= false;
									$res["sigout_abcent"]=    true;
								}
								if($logs[1]->created_at->gt($morningLimit) && $logs[1]->created_at->lt($noonLimit) ){
									//12点 到 13点 @15
									$res["abcent"] = 0.5;
									$res["late"] = 0.5;
									$res["sigin_abcent"]= false;
									$res["sigout_abcent"]=    true;
								}
								if($logs[1]->created_at->gt($noonLimit) && $logs[1]->state==AttendantLog::STATE_EARLY ){
									//13点 到 17点 @16
									$res["early"] = 0.5;
									$res["late"] = 0.5;
								}
							}
								
						}
					}
				}// late <9:00
				if( $logs[0]->created_at->gt($morningLimit)  && $logs[0]->created_at->lt($noonLimit) ){
					if( !isset( $logs[1] ) || $logs[1]->state==AttendantLog::STATE_ABCENT  ){
					
						//如果不存在签退记录 缺勤 @2 @2->9
						$res["early"] = 0.5;
						$res["abcent"] = 0.5;
						$res["sigin_abcent"]=true ;
						$res["sigout_abcent"]=  false  ;
					
					}else{
						if($logs[1]->type==AttendantLog::TYPE_OUTDOOR || $logs[1]->type==AttendantLog::TYPE_HOLIDAY || $logs[1]->type==AttendantLog::TYPE_VOCATION ){
							//@35 @36 @37  @2->8
							$res["normal"] = 0.5;
							$res["abcent"] = 0.5;
							$res["sigin_abcent"]=true ;
							$res["sigout_abcent"]=  false  ;
							if($logs[1]->type==AttendantLog::TYPE_OUTDOOR){
								$outs++;
							}
							if($logs[1]->type==AttendantLog::TYPE_HOLIDAY){
								$holiday +=0.5;
							}
							if($logs[1]->type==AttendantLog::TYPE_VOCATION){
								$vocation +=0.5;
							}
						}else{
								
							if( $logs[1]->state == AttendantLog::STATE_ABCENT  ){
								//全勤 @2->26
								$res["late"] = 0.5;
								$res["abcent"] = 0.5;
								$res["sigin_abcent"]= false;
								$res["sigout_abcent"]=    true;
							}
							if($logs[1]->state==AttendantLog::STATE_NOMAL ){
								// @29
								$res["abcent"] = 0.5;
								$res["normal"] = 0.5;
								$res["sigin_abcent"]=true ;
								$res["sigout_abcent"]=  false  ;
							}
								
							if( $logs[1]->state == AttendantLog::STATE_EARLY  ){
								//$earlyHour = $logs[1]->created_at->hour;
									
								if($logs[1]->created_at->gt($noonLimit) ){
									//@28
									$res["early"] = 0.5;
									$res["abcent"] = 0.5;
									$res["sigin_abcent"]=true ;
									$res["sigout_abcent"]=  false  ;
								}
							}
								
						}
					}
				}// 9:00 < late <12
				if($logs[0]->created_at->gt($noonLimit)){
					if( !isset( $logs[1] ) || $logs[1]->state==AttendantLog::STATE_ABCENT  ){
					
						//如果不存在签退记录 缺勤 @2 @2->10
						$res["abcent"] = 1;
						$res["sigin_abcent"]=true ;
						$res["sigout_abcent"]=  true  ;
					}else{
						if($logs[1]->type==AttendantLog::TYPE_OUTDOOR  || $logs[1]->type==AttendantLog::TYPE_HOLIDAY || $logs[1]->type==AttendantLog::TYPE_VOCATION ){
							//@35 @36 @37 @48 @49 @2->12
							$res["normal"] = 0.5;
							$res["abcent"] = 0.5;
							$res["sigin_abcent"]=false ;
							$res["sigout_abcent"]=  true  ;
							if($logs[1]->type==AttendantLog::TYPE_OUTDOOR){
								$outs++;
							}
							if($logs[1]->type==AttendantLog::TYPE_HOLIDAY){
								$holiday +=0.5;
							}
							if($logs[1]->type==AttendantLog::TYPE_VOCATION){
								$vocation +=0.5;
							}
						}else{
							if( $logs[1]->state == AttendantLog::STATE_ABCENT  ){
								//全勤 @2->9
								$res["late"] = 0.5;
								//$res["early"] = 0.5;
								$res["abcent"] = 0.5;
								$res["sigin_abcent"]=false ;
								$res["sigout_abcent"]=  true  ;
							}
							if( $logs[1]->state == AttendantLog::STATE_EARLY  ){
								if($logs[1]->created_at->gt($noonLimit) && $logs[1]->state==AttendantLog::STATE_EARLY ){
									//@40
									$res["early"] = 0.5;
									$res["abcent"] = 0.5;
									$res["sigin_abcent"]=true ;
									$res["sigout_abcent"]=  false  ;
								}
							}
							if( $logs[1]->state==AttendantLog::STATE_NOMAL  ){
								//@41
								$res["late"] = 0.5;
								$res["abcent"] = 0.5;
								$res["sigin_abcent"]=false ;
								$res["sigout_abcent"]=  true  ;
							}
						}
					}
				} //late > 12
			}////state normal

		}//签到
		if($logs[0]->type==AttendantLog::TYPE_OUTDOOR || $logs[0]->type==AttendantLog::TYPE_HOLIDAY || $logs[0]->type==AttendantLog::TYPE_VOCATION){
			if($logs[0]->type==AttendantLog::TYPE_OUTDOOR){
				$outs++;
			}
			if($logs[0]->type==AttendantLog::TYPE_HOLIDAY){
				$holiday +=0.5;
			}
			if($logs[0]->type==AttendantLog::TYPE_VOCATION){
				$vocation +=0.5;
			}
			//只要外勤可以提交成功，这条记录肯定有效
			if(!isset( $logs[1] ) || $logs[1]->state==AttendantLog::STATE_ABCENT ){
				//如果不存在签退记录  @10 @2->2 @2->4 
				$res["abcent"] = 0.5;
				$res["normal"] = 0.5;
				$res["sigin_abcent"]=false ;
				$res["sigout_abcent"]=  true  ;
			}else{
				
				if($logs[1]->type==AttendantLog::TYPE_OUTDOOR || $logs[1]->type==AttendantLog::TYPE_HOLIDAY || $logs[1]->type==AttendantLog::TYPE_VOCATION){
					//@ 11 @12 @13   @2->28
					$res["normal"] = 1;
					if($logs[1]->type==AttendantLog::TYPE_OUTDOOR){
						$outs++;
					}
					if($logs[1]->type==AttendantLog::TYPE_HOLIDAY){
						$holiday +=0.5;
					}
					if($logs[1]->type==AttendantLog::TYPE_VOCATION){
						$vocation +=0.5;
					}
				}else{
					if( $logs[1]->state == AttendantLog::STATE_NOMAL  ){
						//全勤 @9 @21 @33
						$res["normal"] = 1;
					}
					if( $logs[1]->state == AttendantLog::STATE_EARLY  ){
						//$earlyHour = $logs[1]->created_at->hour;
						if($logs[1]->created_at->lt($morningLimit) ){
							//9点 到 12点 @6  @18
							$res["normal"] = 0.5;
							$res["abcent"] = 0.5;
							$res["sigin_abcent"]=false ;
							$res["sigout_abcent"]=  true  ;
						}
						if($logs[1]->created_at->gt($morningLimit) && $logs[1]->created_at->lt($noonLimit) ){
							//12点 到 13点 @7 @19  @2->6
							$res["abcent"] = 0.5;
							$res["normal"] = 0.5;
							$res["sigin_abcent"]=false ;
							$res["sigout_abcent"]=  true  ;
						}
						if($logs[1]->created_at->gt($noonLimit) && $logs[1]->state==AttendantLog::STATE_EARLY ){
							//13点 到 17点 @8 @ 20 @32
							$res["early"] = 0.5;
							$res["normal"] = 0.5;
						}
					}
				}
				
			}

		}
	
		return [$outs,$res,$vocation,$holiday];
		
	}
	
	public function logs()
	{
		return $this->hasMany(AttendantLog::class,'attendant_id','id');
	}
	
	
	public function department()
	{
		return $this->belongsTo(Department::class,'depart_id','id');
	}
	
	public function rule()
	{
		return $this->belongsTo(AttendantRule::class,'rule_id','id');
	}
	
	public function employee()
	{
		return $this->belongsTo(Employee::class,'employee_id','id');
	}
	
	public function vocation()
	{
		return $this->belongsTo(Vocation::class,'vocation_id','id');
	}
	
	/**
	 * 根据日期获取当天的考勤日志
	 * @param unknown $dt1
	 * @return unknown
	 */
	/* public function attendantLogsOfDate($dt1){
		$endOfDay = $dt1->endOfDay()->toDateTimeString();
		$startOfDay = $dt1->startOfDay()->toDateTimeString();
		$logs = $this->logs()->get();
		return $logs;
	} */
	
	
	/**
	 * 根据 log数量，判断当前考勤数据所处状态
	 */
	public static function getAttendantState($logs){
		$logCount = count($logs);
		if($logCount==0){
			return 1; //还未考勤
		}
		if($logCount==1   ){
			return 2; //考勤中  还未结束
		}
		if($logCount==2){
			return 3; // 考勤结束
		}
	}
	
	
	public  function delete(){
		
    	 $this->logs()->where("attendant_id",$this->id)->delete();
    	return parent::delete();
	}
	
	const STEP_WAIT=1;//还未考勤
	const STEP_SIGIN=2;//已签到 包含外勤 
	const STEP_SIGOUT=3;//已签退  包含外勤 
	
	/* public function checkCurrentAttendantStep($logs){
		//根据优先级
		//首先看看有没有休假
		if( count($logs) == 0 ){
			return static::STEP_WAIT;
		}else{
			if(isset() $logs[0]->type==AttendantLog::TYPE_SIGIN || $logs[0]->type==AttendantLog::TYPE_OUTDOOR ){
				//上午有一条签到类型日志（外勤提交的时候已经做过限制，所以这里要么是签到） 
				return static::STEP_SIGIN;
			}
			if( isset($logs[1] ) && (  ) ){
				//上午有一条签到类型日志（无论多晚 只可能存在一条 因为操作上限制了，）
				return static::STEP_SIGIN;
			}
			else{
				//或者 上午提交了一条外勤
				 $morningLogs = $logs->search(function ($log) {
					return $logs->type==AttendantLog::TYPE_OUTDOOR&&$logs->created_at->hour>12;
				}); 
				
			}
		}
	} */



	public  static function countCurrentDay(){
		$dt1 = Carbon::now();
		$endOfDay = $dt1->endOfDay()->toDateTimeString();
		$startOfDay = $dt1->copy()->startOfDay()->toDateTimeString();
		$attendants = Attendant::whereBetween("created_at" , [$startOfDay,$endOfDay] )->get();
		$attendants->each(function($attendant){
			$res = $attendant->getAttendantResult();
			$attendant->result = serialize($res);
			$attendant->save();
		});
	}
	
	//l 计划任务 将所有今天没有签到的人全部补齐数据  23:00   
	public  static function autoCheckAttendant(){
		
		$rules = AttendantRule::all();
		$deps = Department::where("id","<>",0)->get();
		
			$inTime = Carbon::now();
			$outTime = Carbon::now();
			foreach($deps as $d){
					$eps = $d->employee();
					foreach ($eps as $emploee){
						
							$inTime->addSecond(1);
							$outTime->addSecond(2);
							
							$dt1 = Carbon::now();
							$attendant =  $emploee->attendantOfDate($dt1);
							$attendantLogs = isset($attendant)?$attendant->logs:null;//
							//判断个数 规避缺勤人员不会在现场 绑定不了规则的问题 
							
							if($attendant&& count($attendantLogs)==1){
								
								//
								$isholiday = Vocation::todayIsHoliday($emploee->id,null,$dt1->format("Y-m-d 15:00:00"));
								
								$data = [];
								$data["rule_id"] = $attendantLogs[0]->rule_id;
								$data["depart_id"] = $attendant->depart_id;
								$data["employee_id"] = $emploee->id;
								$data["employee_name"] = $emploee->name;
								$data["latitude"] = $attendantLogs[0]->latitude;
								$data["longitude"] = $attendantLogs[0]->longitude;
								$data['amount'] = ($isholiday)?0.5:0;
								$data['state'] = ($isholiday)?AttendantLog::STATE_NOMAL:AttendantLog::STATE_ABCENT; //如果是假日，正常
								$data["target_address"] ="无指定签到地点";
								if($isholiday instanceof VocationDays){
									$data["vocation_id"] = $isholiday->id;
								}
								$data["log_time"] =$inTime->toDateString() ;
								$data["type"] = $this->getLogType($isholiday,AttendantLog::TYPE_SIGOUT);
								$data["created_at"]=($isholiday)? Carbon::now()->hour(6)->toDateTimeString()  : $outTime->toDateTimeString();
								$attendant->logs()->create($data);

								$attendant->count = 2;
								$attendant->state = 2;
								$attendant->save();
								
							}elseif(!isset($attendant)){
								
								$isAmHoliday = Vocation::todayIsHoliday($emploee->id,null,$dt1->format("Y-m-d 10:00:00") );
								$isPmHoliday = Vocation::todayIsHoliday($emploee->id,null,$dt1->format("Y-m-d 15:00:00") );
								$data = [];
								$data["rule_id"] = 0;
								$data["month"] = $inTime->month;
								$data["year"] = $inTime->year;
								$data["day"] = $inTime->day;
								$data["quarter"] = $inTime->quarter;
								$data["week"] = $inTime->dayOfWeek;
								$data["depart_id"] = $d->id;
								$data["depart_name"] =$d->name ;
								$data["employee_id"] = $emploee->id;
								$data["employee_name"] = $emploee->name;
								$data["total"] =  ($isAmHoliday&&$isPmHoliday)?1:0;
								$data["count"] = 2;
								$data["state"] = ($isAmHoliday&&$isPmHoliday)?3:1;
								$data["created_at"]=$outTime->toDateTimeString();
							    /* if($isholiday!==false&&$isholiday!==true){
									$data["vocation_id"] = $isholiday->id;
								}  */
								$attendant = Attendant::create($data);
								
								$data = [];
								$data["rule_id"] = 0;
								$data["depart_id"] = $attendant->depart_id;
								$data["employee_id"] = $emploee->id;
								$data["employee_name"] = $emploee->name;
								$data["latitude"] = "";
								$data["longitude"] = "";
								$data['amount'] =($isAmHoliday)?0.5:0;
								
								$data["target_address"] ="无指定签到地点";
								
								$data['state'] = ($isAmHoliday)?AttendantLog::STATE_NOMAL:AttendantLog::STATE_ABCENT;
								if($isAmHoliday instanceof VocationDays){
								 	$data["vocation_id"] = $isAmHoliday->id;
								}  
								$data["created_at"]=($isAmHoliday)?Carbon::now()->hour(6)->toDateTimeString():$inTime->toDateTimeString();
								$data["log_time"] =$inTime->toDateString();
								$data["type"] = static::getLogType($isAmHoliday,AttendantLog::TYPE_SIGIN);
								$attendant->logs()->create($data);
								
								$data['state'] = ($isPmHoliday)?AttendantLog::STATE_NOMAL:AttendantLog::STATE_ABCENT;
								if($isPmHoliday instanceof VocationDays){
									$data["vocation_id"] = $isPmHoliday->id;
								}
								$data["created_at"]=($isPmHoliday)?Carbon::now()->addSeconds(1)->hour(19)->toDateTimeString():$outTime->toDateTimeString();
								$data["log_time"] =$outTime->toDateString();
								$data["type"] = static::getLogType($isPmHoliday,AttendantLog::TYPE_SIGOUT);
								$attendant->logs()->create($data);

								
							}
							
					}
			}
	}
	
	public static  function getLogType($holiday,$type){
		
		if($holiday===true){
			return AttendantLog::TYPE_HOLIDAY;
		}
		if($holiday instanceof  VocationDays){
			return AttendantLog::TYPE_VOCATION;
		}
		if($holiday===false){
			return  $type ;
		}
	}
	public  function makeAbcentLog($attendant,$type,$isholiday,$logtime){
		
		$data = [];
		$data["rule_id"] = 0;
		$data["depart_id"] = $attendant->depart_id;
		$data["employee_id"] = $attendant->employee_id;
		$data["employee_name"] = $attendant->employee_name;
		$data["latitude"] = "";
		$data["longitude"] = "";
		$data['amount'] =($isholiday)?0.5:0;
		$data['state'] = ($isholiday)?1:5; //如果是假日，正常
		$data["target_address"] ="无制定签到地点";
		
		$data["created_at"]= $logtime;
		$data["log_time"] =$logtime ;
		$data["type"] =$type;
		
		$attendant->logs()->create($data);
		
	}
	
	// 计划任务  自动考勤
	public static function specialAttendant(){
		
		//获取所有规则
		$rules = AttendantRule::all();
		
		$rules->each(function($rule){
			 //自动考勤
			 //
			$inTime = Carbon::createFromFormat("H:i", $rule->sigin_time);
			$outTime = Carbon::createFromFormat("H:i", $rule->sigout_time);
			 $auto = !empty($rule->auto_attend)?explode(",",$rule->auto_attend):[];
			 foreach ($auto as $a){
			 	$inTime->addSecond(1);
			 	$outTime->addSecond(1);
			 	$emploee = Employee::find($a);
			 	$isAmHoliday = Vocation::todayIsHoliday($emploee->id,null,$inTime->format("Y-m-d 10:00:00") );
			 	$isPmHoliday = Vocation::todayIsHoliday($emploee->id,null,$outTime->format("Y-m-d H:i:s") );
			 	if($emploee){
			 		$dep = $emploee->getDepartment();
			 		$data = [];
			 		$data["rule_id"] = $rule->id;
			 		$data["month"] = $inTime->month;
			 		$data["year"] = $inTime->year;
			 		$data["day"] = $inTime->day;
			 		$data["quarter"] = $inTime->quarter;
			 		$data["week"] = $inTime->dayOfWeek;
			 		$data["depart_id"] = $dep->id;
			 		$data["depart_name"] =$dep->name ;
			 		$data["employee_id"] = $emploee->id;
			 		$data["employee_name"] = $emploee->name;
			 		$data["total"] =  ($isAmHoliday&&$isPmHoliday)?1:0;
			 		$data["state"] =  ($isAmHoliday&&$isPmHoliday)?3:1;
			 		$data["created_at"]=$inTime->toDateTimeString();
			 		$data["updated_at"]=$inTime->toDateTimeString();
			 		$data["count"] = 2;
			 		$attendant = Attendant::create($data);
			 		
			 		$data = [];
			 		$data["rule_id"] = $rule->id;
			 		$data["depart_id"] = $attendant->depart_id;
			 		$data["employee_id"] = $emploee->id;
			 		$data["employee_name"] = $emploee->name;
			 		$data["latitude"] = $rule->latitude;
			 		$data["longitude"] = $rule->longitude;
			 		$data['amount'] = 0.5;
			 		$data['state'] = 1;
			 		$data["target_address"] =$rule->position;
			 		
			 		if($isAmHoliday instanceof VocationDays){
			 			$data["vocation_id"] = $isAmHoliday->id;
			 		}
			 		$data["log_time"] =$inTime->toDateString() ;
			 		$data["type"] = static::getLogType($isAmHoliday,AttendantLog::TYPE_SIGIN);
			 		$log1 = $attendant->logs()->create($data);
			 		$log1->created_at=$inTime->toDateTimeString();
			 		$log1->updated_at=$inTime->toDateTimeString();
			 		$log1->save();
			 		
			 		if($isAmHoliday instanceof VocationDays){
			 			$data["vocation_id"] = $isPmHoliday->id;
			 		}
			 		$data["log_time"] =$outTime->toDateString() ;
			 		$data["type"] = static::getLogType($isPmHoliday,AttendantLog::TYPE_SIGOUT);
			 		$log2 = $attendant->logs()->create($data);
			 		$log2->created_at=$outTime->toDateTimeString();
			 		$log2->updated_at=$outTime->toDateTimeString();
			 		$log2->save();
			 	}
			 }
			 //if($rule->sigin_time)
			 
		});
	}
	
}
