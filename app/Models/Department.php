<?php

namespace App\Models;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Stoneworld\Wechat\Group;
use Stoneworld\Wechat\Broadcast;
use Stoneworld\Wechat\Message;
use Stoneworld\Wechat\Messages\NewsItem;
use Carbon\Carbon;
class Department  extends Model
{
	use ModelTree, AdminBuilder;
	

	protected $table = 'department';

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	
		$this->setParentColumn('parentid');
		$this->setOrderColumn('order');
		$this->setTitleColumn('name');
	}

    /**
     * 获取部门的审核人
     */
	public function charger(){
        return $this->belongsTo(Employee::class,'charge','id');
    }

	public  function employee( ){
		return Employee::whereRaw("find_in_set('$this->id',department)")->get();
	}
	
	/* public  function save(){
	  //看看id是否存在
	} */
	
	public  function delete(){
		$group = new Group(config("ent.appId"), config("ent.secret"));
		$res = $group->delete($this->id);
		if( $res["errmsg"] && $res["errmsg"]=="deleted" ){
			parent::delete();
		}else{
            throw new \ErrorException( $res["errmsg"] );
        }
	}
	
	public static function getAllLeaders(){
		return $leaders = Employee::where("position","部门正职")->get();
	}

    public static function getLeaders(){
        $deps = static::where("leaders","<>","")->get();
        $employees = [];
        foreach ($deps as $dep){
            $employees = array_merge($employees,explode(",",$dep->leaders) ) ;
        }
        return Employee::whereIn("id",$employees)->get();
    }
	public function getLeader(){
		return  Employee::where("position","部长")->whereRaw("find_in_set('$this->id',department)")->get();
	}
	
	public  function ReportToDepartLeader(){
		$dt = Carbon::now()->subDay(); 
		$start = $dt->startofDay()->toDateTimeString();
		$end = $dt->endofDay()->toDateTimeString();
		$logs = AttendantLog::where("depart_id",$this->id)->whereBetween("created_at",[$start,$end]);
		
	}
	
	//
	/**
	 *推送报告给领导 
	 * @param unknown $type 1 周报告 2 日报告
	 * @param unknown $dep
	 * @return boolean
	 */
	public static  function pushReportToDepartLeader($type,$dep){

		$dep = Department::find($dep);
		if(!isset($dep)){
			return false;
		}
		$leaders = $dep->getLeader();
		if(count($leaders)==0) return false;
		
		$broadcast = new Broadcast( config("ent.appId"), config("ent.secret"));
		$newsItem = new NewsItem();
		$newsItem->title = ($type==1) ?'周考勤报告':"日考勤报告";
		$newsItem->description =  ($type==1)?"上一周考勤统计":"上一日考勤统计";
		$newsItem->pic_url = 'http://iph.href.lu/600x400';
		$newsItem->url = route("statics");
		$message = Message::make('news')->item($newsItem);
		$ps = $leaders->pluck('userid')->all();
		$res = $broadcast->fromAgentId(config('ent.attendant_chat'))->send($message)->to($ps);
		return $res;

	}
	
	/**
	 * 部门通知计划任务 1 上下班 签到通知  2 领导通知
	 */
	public static function departNotifyScheduel($type=1){
		//获取所有等待通知的部门
		//获取部门的所有员工
		//检查当前时间是否符合通知要求,
		$dt1 = Carbon::now();
		$endOfDay = $dt1->endOfDay()->toDateTimeString();
		$startOfDay = $dt1->copy()->startOfDay()->toDateTimeString();
		$depsShouldNotify = Department::where("notify",1)->get();
		$dt2 = Carbon::now();
		
		foreach ( $depsShouldNotify as $dep ){
			if($dep->id==1){
				continue;
			}
				
				if($type==1){
					if( !empty($dep->office_time)  ){
						$ot = Carbon::createFromFormat("H:i", $dep->office_time)->diffInMinutes($dt2);
						if( $ot<0 && $ot>-5 ){
							$alreadyNotify = NotifyLog::isNotifiedToday( $dep->id, 1 );
							//推送通知
							if(!$alreadyNotify){
								static::notifyEmployeeAttendant( 1 , $dep->id );
								NotifyLog::log($dep->id, 1);
							}
						}
						if(  $ot<-30 ){
							//上班后30分钟 发送前一日考勤报告
							$alreadyNotify = NotifyLog::isNotifiedToday( $dep->id, 2 );
							if(!$alreadyNotify){
								Statics::pushDayOrWeekReportToLeaderOfDep(false);
								NotifyLog::log($dep->id, 2);
							}
						}
					}	
				}
				
				if($type==2){
					NotifyLog::isNotifiedToday( $dep->id, [$endOfDay,$startOfDay] );
					if( !empty($dep->quit_time)  ){
						
						$qt = Carbon::createFromFormat("H:i", $dep->quit_time)->diffInMinutes($dt2);

						if( $qt>0 && $qt<5 ){
							//推送通知
							
							$alreadyNotify = NotifyLog::isNotifiedToday( $dep->id, 3 );
							dd($alreadyNotify);
							if(!$alreadyNotify){
								static::notifyEmployeeAttendant( 2 , $dep->id );
								NotifyLog::log($dep->id, 3);
							}
						}
					}
				}
				
				 //static::notifyEmployeeAttendant($type, $dep)
		}
		
	}
	
	//计划任务  每天提醒 上班前5分钟 下班后5分钟  推送部门考勤通知
	public  static function notifyEmployeeAttendant($type,$dep){
		$broadcast = new Broadcast( config("ent.appId"), config("ent.secret"));
		/* $deps = Department::all(); */
		if($type==1){
			//上班
			$msg = "还有5分钟就要上班了";
		}
		if($type==2){
			//下班
			$msg ="已经下班了5分钟了，注意签退";
		}
		$newsItem = new NewsItem();
		$newsItem->title = '签到提醒';
		$newsItem->description = $msg;
		$newsItem->pic_url = 'http://nqi.iselab.cn/img/message.png';
		$newsItem->url = route("rules");
		$message = Message::make('news')->item($newsItem); 
		$res = $broadcast->fromAgentId(config('ent.attendant_chat'))->send($message)->toParty($dep);
		return $res;
	}
	
}
