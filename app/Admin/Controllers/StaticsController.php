<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Chart\Bar;
use Encore\Admin\Widgets\Chart\Doughnut;
use Encore\Admin\Widgets\Chart\Line;
use Encore\Admin\Widgets\Chart\Pie;
use Encore\Admin\Widgets\Chart\PolarArea;
use Encore\Admin\Widgets\Chart\Radar;
use Encore\Admin\Widgets\Collapse;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use App\Models\Department;
use Encore\Admin\Widgets\Form;
use App\Models\AttendantLog;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Attendant;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Model;
use App\Models\Vocation;
use App\Models\Statics;

class StaticsController extends Controller
{
    public function index()
    {
    	//$departs = Department::all();
    	//return response()->view("admin.reports",['deps'=>$departs]);

       $time = isset($this->data["time"])?$this->data["time"]:"";
       $dep = isset($this->data["dep"])?$this->data["dep"]:0;
      return Admin::content(function (Content $content)use($time,$dep) {

            $content->header('数据统计');
            $content->description('统计概览');
		
            $content->row(function (Row $row)use($time,$dep) {
                 $row->column(12, function (Column $column)use($time,$dep) {
                 if( !Admin::user()->isAdministrator() ){
                     $deps = explode(",",Admin::user()->department);
                     $departs = Department::whereIn("id",$deps)->get();
                 }else{
                     $departs = Department::all();
                 }

                 $column->append( view('admin.reports', ['deps'=>$departs,'time'=>$time,'dep'=>$dep])->render() );
                });
            });
            Admin::css('/packages/admin/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');
            Admin::js('/packages/admin/moment/min/moment-with-locales.min.js');
            Admin::js('/packages/admin/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
            Admin::script($this->reportsScript());
        }); 
      	
    } 
       
    public function vocationStatics(){
    	
    	return Admin::content(function (Content $content) {
    	
    		$content->header('请假统计');
    		$content->description('请假统计报表');
    	
    		$content->row(function (Row $row) {
    	
	    			$row->column(6, function (Column $column) {
                        if( !Admin::user()->isAdministrator() ){
                            $deps = explode(",",Admin::user()->department);
                            $departs = Department::whereIn("id",$deps)->get();
                        }else{
                            $departs = Department::all();
                        }
	    				$column->append(view('admin.vocation_reports', ['deps'=>$departs])->render());
	    			});
    	
    				$row->column(6, function (Column $column) {
    					$column->append(view('admin.reports_chart')->render());
    				});
    		});
    	});
    }
    
    public function monthly()
    {
    	$time = isset($this->data["time"])?$this->data["time"]:1;
    	return Admin::content(function (Content $content)use($time) {
    
    		$content->header('月统计报表');
    		$content->description('');
    
    		$content->row(function (Row $row)use($time) {
    			$row->column(12, function (Column $column)use($time) {
                    if( !Admin::user()->isAdministrator() ){
                        $deps = explode(",",Admin::user()->department);
                        $departs = Department::whereIn("id",$deps)->get();
                    }else{
                        $departs = Department::all();
                    }
    				$column->append( view( 'admin.monthly_reports', [ 'time'=>$time , 'deps'=>$departs  ] )->render() );
    			});
    		});
    		Admin::script($this->reportsScript());
    	});
    		 
    }
    
    public function monthlyExcel(){
    	$request = app('request');
    	//每个月 然后生成每天的区间，然后以人为单位，依次生成每天区间
    	$month =  $request->input('month');
    	$dep =  $request->input('dep');
        if(!Admin::user()->isAdministrator()){
            $deps = explode(",",Admin::user()->department);
            $res = in_array($dep,$deps);
            if(!$res){
                throw new \Exception('非法访问');
            }
        }
    	$n = Carbon::now("Asia/Shanghai")->month($month);
    	//先生成
    	$startMonth =$n->startOfMonth();
    	$endMonth = $n->endOfMonth();
    	
    	list($overview,$detail) = $this->makeAttendantMonthlyStatics( $dep, $startMonth , $endMonth );
    	//获取所有员工

    }
    
    public  function makeAttendantMonthlyStatics($dep,$start,$end){
    	//Admin::user()->department
    	if(!Admin::user()->isAdministrator()){
    		$deps = explode(",",Admin::user()->department);
            $res = in_array($dep,$deps);
            if(!$res){
                throw new \Exception('非法访问');
            }
    	}
    	$month = $start->month;
    	$endDayOfMonth = $end->day;
    	if($dep==0){
    		$attendants = Attendant::where('month',$month)->orderBy("created_at","desc")->with("logs")->get()->groupBy("employee_name");
    	}else{
    		$attendants = Attendant::where('month',$month)->where("depart_id",$dep)->with("logs")->orderBy("created_at","desc")->get()->groupBy("employee_name");
    	}
		
    	$dataset = [];
    	$index = 1;
    	$holidays = Vocation::getHolidaysOfMonth($month);
    	$workdays = $endDayOfMonth-count($holidays);
    	foreach ($attendants as $employee=>$at){
    		$personRecords = $at->keyBy("day");
    		$item["序号"] = $index;	
    		$item["部门"] = $at[0]->depart_name;
    		$item["姓名"] = $employee;
    		
    		$noAtd = 0;
    		for($i=1 ; $i<=$endDayOfMonth ; $i++){
    			
    			if( !isset($personRecords[$i] ) ){
    				//先不删，这里是实时分析数据，可能记录还未生成
    				$key = $i<10?"0".$i:$i;
    				if( isset($holidays[$key] ) ){
    					//如果没有记录 先认为缺勤1天
    					$item[$i] = "休";
    					continue;
    				}
    				//如果没有记录 先认为缺勤1天
    				$item[$i] = "✕";
    				$noAtd++;
    				continue;
    			}else{
    				list($outs,$counts,$vocation,$holiday) = $personRecords[$i]->getAttendantResult();
    				if( $vocation !=0 || $holiday!=0 ){
    					$item[$i] = "休";
    				}
    				if( $counts["normal"] != 1 ){
    					$item[$i] = 1- $counts["normal"];
    				}else{
    					$item[$i] = "√";
    				}	
    			}
    			
    		}
    		$item["缺勤"] = $counts["abcent"]."天";
    		$item["应出勤天数"] = $workdays."天";
    		$item["迟到"] = $counts["late"]."天";
    		$item["早退"] = $counts["early"]."天";
    		$item["病事假天数"] = $vocation."天";
    		$item["实际出勤天数"] = ($workdays-$vocation-$noAtd-$holiday-$counts["abcent"])."天";
    		$dataset[] = $item;
    		
    		$index++;
    		
    	} 
    	
    	Excel::create($month."月出勤统计".Carbon::now()->format("Y-m-d H:i:s"), function($excel)use($dataset,$month,$endDayOfMonth) {
    	
    		$excel->sheet('月出勤统计', function($sheet)use($dataset,$month,$endDayOfMonth){
    			$dt = Carbon::now();
    			$sheet->loadView('report.monthly',["data"=>$dataset,"month"=>$month,"daysOfMonth"=>$endDayOfMonth,"dt"=>$dt]);
    			//$sheet->fromArray($dataset,null, 'A1', true);
    		});

    	})->download('xls');
    	
    }
    
    public function overviewExcel(){
    	$request = app('request');
    	$dep =  $request->input('dep');
    	$span = [$this->data["time1"],$this->data["time2"]];
    	/* $time =  $request->input('time');
    	$dt = Carbon::now();
    	$begin = $dt->endOfDay();
    	switch ($time){
    		case 1:
    			$end = $dt->copy()->startOfDay();
    			break;
    		case 2:
    			$end = $dt->copy()->subDays(3);
    			break;
    		case 3:
    			$end = $dt->copy()->subWeekday();
    			break;
    		case 4:
    			$end = $dt->copy()->subWeekdays(2);
    			break;
    		case 5:
    			$end = $dt->copy()->subMonth();
    			break;
    		case 6:
    			$end = $dt->copy()->subMonths(3);
    			break;
    	} */
    	/* if($time>1){
    		$span = [$end->toDateTimeString(),$begin->toDateTimeString()];
    	}else{
    		$span = [$begin->toDateTimeString(),$end->toDateTimeString()];
    	} */
    	
    	//$span = [$end->toDateTimeString(),$begin->toDateTimeString()];
    	
    	list($sheet1Data,$sheet2Data,$sheet3Data) = Statics::makeAttendantStatics($dep,$span);
		//dump($sheet1Data);
		//dump($sheet2Data);
		//dd($sheet3Data);
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
				    			"人均迟到次数"=>($workers)?round($data['lateCount']/$workers,2):0 ,
				    			"人均早退次数"=>($workers)?round($data['earlyCount']/$workers,2):0 ,
				    			"人均缺勤天数"=>($workers)?round($data['abcentDays']/$workers,2):0,
				    			"人均外勤次数"=>($workers)?round($data['outCuont']/$workers,2):0
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
    
    public function reportsScript(){
    	$ovurl = route('overview_reports');
    	$excel = route('overview_excel');
    	return <<<'EOT'
    	  $(function(){
    			$('.starttime').datetimepicker({"format":"YYYY-MM-DD ","locale":"zh_CN"});
	            $('.endtime').datetimepicker({"format":"YYYY-MM-DD ","locale":"zh_CN","useCurrent":false});
	            $(".starttime").on("dp.change", function (e) {
	                $('.endtime').data("DateTimePicker").minDate(e.date);
	            });
	            $(".endtime").on("dp.change", function (e) {
	                $('.starttime').data("DateTimePicker").maxDate(e.date);
	            });
    	 });
    	  window.query = function(){
    			//alert('test');
    			var param = {};
    			var $dep = $("#dep").val();
    			param.dep = $dep ;
    			var $time = $("#starttime").val();
    			if($time!=""){
    				param.time1 = $time;
    			}else{
    				alert("输入开始时间！");
    			    return false;
    			}
    			
    			var $time2 = $("#endtime").val();
    			if($time2!=""){
    				param.time2 = $time2;
    			}else{
    				alert("输入结束时间！");
    				return false;
    			}

    			
    			layer.load(1);
    			 $.get("/admin/api/reports",param).done(function(data){
    				var overview  = {
	    				"full":[], //全勤列表,
	    				"late":[], //迟到列表,
	    				"early":[], //早退列表,
	    				"abcent":[], //缺勤列表,
	    				"out":[],//外勤
	    			};
    				console.log(data);
    				var late = 0;
    				var early = 0;
    				var abcent = 0;
    				var out = 0;
    				if(data.detail&&data.overview){
    						$("#print").show();
    						$("#overview-area").show();
    						$("#print").attr("href","/admin/overview/reports/excel?time1="+ $time +"&time2="+$time2+"&dep=" + $dep );
	    					_.forEach(data.overview,function(item){
	    							if(item.isFull=="是"){
	    								overview.full.push(item);
	    							}
    								if(item.earlyTimes>0){ overview.early.push(item);late += early.earlyTimes; }
    								if(item.lateTimes>0){ overview.late.push(item); late += item.lateTimes; }
    								if(item.outTimes>0){ overview.out.push(item); out += item.outTimes;  }
    								if(item.abcentDays>0){ overview.abcent.push(item); abcent += item.abcentDays; }
    
	    					});
    						$("#fullTime").html( overview.full.length );
    						$("#lateCount").html( late );
    						$("#earlyCount").html( early );
    						$("#abcentCount").html( abcent );
    						$("#outCount").html( out );
    			
    						$("#fullArea").html("");
    						_.forEach(overview.full,function(item){
    							$("#fullArea").append("<span style='margin-right:5px;margin-bottom:5px;'>"+ item.employee + "&nbsp;&nbsp;" +"</span>");
	    					});
    						$("#earlyArea").html("");
    						_.forEach(overview.early,function(item){
    							$("#earlyArea").append("<span style='margin-right:5px;margin-bottom:5px;'>"+ item.employee + "&nbsp;&nbsp;" + item.earlyTimes +"天</span>");
	    					});
    						$("#lateArea").html("");
    						_.forEach(overview.late,function(item){
    							$("#lateArea").append("<span style='margin-right:5px;margin-bottom:5px;'>"+ item.employee + "&nbsp;&nbsp;" + item.lateTimes +"天</span>");
	    					});
    						$("#abcentArea").html("");
    						_.forEach(overview.abcent,function(item){
    							$("#abcentArea").append("<span style='margin-right:5px;margin-bottom:5px;'>"+ item.employee + "&nbsp;&nbsp;" + item.abcentDays +"天</span>");
	    					});
							$("#outArea").html("");
    						_.forEach(overview.out,function(item){
    							$("#outArea").append("<span style='margin-right:5px;margin-bottom:5px;'>"+ item.employee + "&nbsp;&nbsp;" + item.outTimes +"天</span>");
	    					});
    							
    						var myChart = echarts.init(document.getElementById('revenue-chart'));

					        // 指定图表的配置项和数据
					        var option = {
					            title: {
					                text: '考勤综合统计'
					            },
					            tooltip: {},
					            legend: {
					                data:['考勤次数']
					            },
					            xAxis: {
					                data: ["全勤","迟到","早退","缺勤","外勤"]
					            },
					            yAxis: {},
					            series: [{
					                name: '销量',
					                type: 'bar',
					                data: [overview.full.length, overview.late.length,overview.early.length, overview.abcent.length , overview.out.length]
					            }]
					        };
    						myChart.setOption(option);
    						layer.closeAll();
    				}
    			});
    	}
    	/* window.print = function(){
    			var $dep = $("#dep").val();
    			var $time = $("#time").val();
    			layer.open({
				  type: 1,
				  skin: 'layui-layer-rim', //加上边框
				  area: ['420px', '240px'], //宽高
				  content: ' button '
				});
    	} */
EOT;
    } 
   
    public function getOverviewReports(){
    	$request = app('request');
	    $dep =  $request->input('dep');
        if(!Admin::user()->isAdministrator()){
            $deps = explode(",",Admin::user()->department);
            $res = in_array($dep,$deps);
            if(!$res){
                throw new \Exception('非法访问');
            }
        }
	    $n = Carbon::now("Asia/Shanghai");
	    $now =$n->toDateTimeString();
	    $startOfDay = $n->startOfDay()->toDateTimeString();
	    $span = [$this->data["time1"] , $this->data["time2"] ];
	    list($overview,$detail) = Statics::makeAttendantStatics($dep,$span);
	    //获取所有员工
	    
	    $people = Employee::whereRaw("find_in_set('{$dep}',department)")->get();
	    
	    //获取所有数据
	    $res = Attendant::where("depart_id",$dep)->whereBetween("created_at",[$startOfDay,$now])->with("logs")->get();
    	//$res  = AttendantLog::where("depart_id",$dep)->whereBetween("created_at",[$startOfDay,$now])->get();
    	
    	return [
    		'overview'=>$overview,
    		'detail'=>$detail,
    	];
    
    }
    
}


