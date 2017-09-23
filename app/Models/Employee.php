<?php

namespace App\Models;
use Stoneworld\Wechat\User;
use Carbon\Carbon;

class Employee  extends Model
{
  
	protected $table = 'employee';
    private $wxuser ;
    
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		
		$this->wxuser = new User( config("ent.appId"), config("ent.secret"));
		
	}

	public function getVocationAccount(){
	    $sets = VocationRule::whereIn("title",["年休假","事假","病假"])->get();
        $temp =$sets->pluck("title","id");
        $ruleIds = $temp->keys()->all();
        $rulePairs = $temp->all();
        $yearVocation = 0;
        $issueVocation = 0;
        $sickVocation = 0;
        $histories = Vocation::where("employee_id",$this->id)->whereIn("vocation_rule_id",$ruleIds)->get()
                    ->groupBy("vocation_rule_id");

        foreach($histories as $key=>$h){
            $title = isset($rulePairs[$key])?$rulePairs[$key]:"";
            switch ($title){
                case "年休假":
                    $yearVocation += count($h);
                    break;
                case "事假":
                    $issueVocation += count($h);
                    break;
                case "病假":
                    $sickVocation += count($h);
                    break;
            }
        }
        //dd([$yearVocation,$issueVocation,$sickVocation]);
        return [$yearVocation,$issueVocation,$sickVocation];
    }

	public  function deps(){
		$deps = explode(",", $this->department);
		return Department::whereIn('id',$deps)->get();
	}
	
	public function getRulesByGPS($gps=[]){
		
		$rules = AttendantRule::getRulesByDep( $this->departmentId() );
		foreach ($rules as $rule){
			$dist = distanceBetween($gps[1], $gps[0], $rule->latitude , $rule->longitude);
			//dump($dist);
			if($dist < $rule->dist_span ){
				return $rule ;
			}
		}
		//dd("test");
		return null;
	}
	
	/**
	 * 根据日期获取当天的考勤日志
	 * @param unknown $dt1
	 * @return unknown
	 */
	
	public  function attendantOfDate(){
		$dt1 = Carbon::now();
		$endOfDay = $dt1->endOfDay()->toDateTimeString();
		$startOfDay = $dt1->copy()->startOfDay()->toDateTimeString();
		$log = Attendant::where("employee_id",$this->id)->whereBetween("created_at" , [$startOfDay,$endOfDay] )->first();
		return $log;
	}
	
	public function getCurrentTime(){
		return  Carbon::now();
	}
	
	/**
	 * 
	 * @param unknown $rule  如果为空 则为外勤
	 * @param unknown $gps
	 * @param string $location 外勤需要保存
	 * @param string $memo  外勤需要保存,
	 * @param boolean 自动生成签到记录
	 * @return multitype:boolean string |multitype:boolean \App\Models\unknown
	 */
	public function attendant($rule,$gps,$location="",$memo="",$time=null){
		//会员签到
		
		\DB::beginTransaction();

		try {
		    
			$updated = false;
			$dt1 = isset($time)?$time:Carbon::now();
			$hour = $dt1->hour; 
			$attendant = $this->attendantOfDate();
			
			if(!isset($attendant)){
				$data = [];
				//还未考勤
				$data["rule_id"] = isset($rule)?$rule->id:0;
				$data["month"] = $dt1->month;
				$data["year"] = $dt1->year;
				$data["day"] = $dt1->day;
				$data["quarter"] = $dt1->quarter;
				$data["week"] = $dt1->dayOfWeek;
				$data["depart_name"] =$this->getDepartment()->name ;
				$data["depart_id"] = $this->departmentId();
				$data["employee_id"] = $this->id;
				$data["employee_name"] = $this->name;
				$data["created_at"] = $dt1->format("Y-m-d H:i:s");
				$data["count"] = 1;
				$attendant = Attendant::create($data);
			}
			//此处其实无须判断，因为界面里面已经判断过了 而且也不应该用state判断，
			/* if($attendant->state == 3){
				//如果休息 返回无须签到
				return [false ," 休假中无须签到"];
			}
			 */
			$logs = $attendant->logs;
			$logCount = count($logs);
			if( $logCount==2 && $logs[1]->type!=AttendantLog::TYPE_OUTDOOR && $logs[1]->type!=AttendantLog::TYPE_SIGOUT){  //&&  $logs[1]->type!=AttendantLog::TYPE_OUTDOOR
				//可无限次签退。 
				return [false ,"今天已经完成考勤"];
			}
			
			$logData = [];
			$logData["rule_id"] = isset($rule)?$rule->id:0;
			$logData["depart_id"] = $attendant->depart_id;
			$logData["depart_name"] =$attendant->depart_name;
			$logData["employee_id"] = $this->id;
			$logData["employee_name"] = $this->name;
			$logData["target_address"] = isset($rule)?$rule->position:"无规定签到地点";
			$logData["latitude"] = $gps[1];
			$logData["longitude"] = $gps[0];
			$logData["log_time"] = $dt1->toDateString();
			$logData["created_at"] = $dt1->format("Y-m-d H:i:s");
			
			//如果是外勤，此处需要更新数据
			if(!empty($location)){
				$logData["location"] = $location;
			}
			if(!empty($memo)){
				$logData["memo"] = $location;
			}
			$logData['amount'] = 0;
			
			if(isset($rule)){
                if( $logCount==2 && $logs[1]->type==AttendantLog::TYPE_OUTDOOR ){
                    return [false ,"已经提交外勤 无须签到"];
                }
				if( $logCount==2 && $logs[1]->type==AttendantLog::TYPE_SIGOUT ){
					//多次签退
					$attendant->total -=  $logs[1]->amount;
					$attendant->state = 2;
					$logs[1]->delete();
					$logCount--;
				}
				if( $logCount==1 ){
					//签退
					$logData["type"] =2;
					$outTime = $rule->getSigoutTime();
					$diff = $outTime->diffInMinutes($dt1,false);
					if( $diff > -($rule->time_span) ){
						//6-6
						$logData['state'] = AttendantLog::STATE_NOMAL;
						$logData['amount'] = 0.5;
					}else{
						// 下班早退 
						$logData['state'] = AttendantLog::STATE_EARLY;
						$logData['amount'] = 0;
					}
				
					
				}
				if( $logCount==0 ){
					//还未签到
					$logData["type"] =1;
					$attendTime = $rule->getSiginTime();
					$diff = $attendTime->diffInMinutes($dt1,false);
					if( $diff < $rule->time_span ){
						//上班前
						$logData['state'] = AttendantLog::STATE_NOMAL;
						$logData['amount'] = 0.5;  //暂时先算半天勤，具体还要看下午签到
					}else{
						//6-6 迟到就是迟到，不算缺勤矿工
						$logData['state'] = AttendantLog::STATE_LATE;
						$logData['amount'] = 0;
						//上班后 迟到
						
					}
				}
			}else{
				//同时生成一个外勤记录
				$od = Outdoor::create([
					"depart_id"=>$logData["depart_id"],
					"depart_name"=>$logData["depart_name"],
					"employee_id"=>$logData["employee_id"],
					"employee_name"=>$logData["employee_name"],
					"target_address"=>$logData["target_address"],
					"latitude"=>$logData["latitude"],
					"longitude"=>$logData["longitude"],
					"location"=>$logData["location"],
					"memo"=>$logData["memo"],
				]);
				$logData["type"] =AttendantLog::TYPE_OUTDOOR;
				if($dt1->hour <= 12){
					//应该成为签到记录
					if( $logCount==1 ){
						
						//签到 换成外勤
						$logs[0]->rule_id = 0; 
						$logs[0]->type = AttendantLog::TYPE_OUTDOOR;
						$logs[0]->target_address = "无规定签到地点";
						$logs[0]->latitude = $logData["latitude"];
						$logs[0]->longitude = $logData["longitude"];
						$logs[0]->log_time = $logData["log_time"];
                        $logs[0]->location = $logData["location"];
						$logs[0]->state = AttendantLog::STATE_NOMAL;
						$attendant->total -= $logs[0]->amount;
						$logs[0]->amount = 0.5;
						$logs[0]->outdoor_id = $od->id;
						$logs[0]->created_at = $dt1->format("Y-m-d H:i:s");
						$logs[0]->save();
						$updated = true;
						$attendantLog = $logs[0];
						//return [false ,"上午已经签到，无法同时在上午提交外勤"];
					}else{
						//出外勤
						$logData['state'] = AttendantLog::STATE_NOMAL;
						$logData['amount'] = 0.5;
					}
				}
				if($dt1->hour >= 12){

					if( $logCount==0 ){
						$isholiday = Vocation::todayIsHoliday($this->id,null,Carbon::now()->format("Y-m-d 8:00:00"));
							//上午要同时生成一条 签到 缺勤的数据

						$temp = [];
						$temp["rule_id"] = 0;
						$temp["depart_id"] = $attendant->depart_id;
						$temp["depart_name"] = $attendant->depart_name;
						$temp["employee_id"] = $this->id;
						$temp["employee_name"] = $this->name;
						$temp["latitude"] = "";
						$temp["longitude"] = "";
						$temp['amount'] =($isholiday)?0.5:0;
						$temp['state'] = ($isholiday)?AttendantLog::STATE_NOMAL:AttendantLog::STATE_ABCENT; //如果是假日，正常
						$temp["target_address"] ="无签到地点";

						$temp["created_at"]=$dt1->toDateTimeString();
						$temp["log_time"] =$dt1->toDateString() ;
						$temp["type"] = (!$isholiday)?AttendantLog::TYPE_SIGIN:AttendantLog::TYPE_VOCATION;
						$attendant->logs()->create($temp);
						$logCount++;
					}

					if( $logCount==2 ){

						//签到 换成外勤
						$logs[1]->rule_id = 0;
						$logs[1]->type = AttendantLog::TYPE_OUTDOOR;
						$logs[1]->target_address = "无规定签到地点";
						$logs[1]->latitude = $logData["latitude"];
						$logs[1]->longitude = $logData["longitude"];
						$logs[1]->log_time = $logData["log_time"];
						$attendant->total -= $logs[1]->amount;
                        $logs[1]->target_address = $logData["location"];
                        $logs[1]->location  = $logData["location"];
						$logs[1]->amount = 0.5;
						$logs[1]->outdoor_id = $od->id;
						$logs[1]->created_at = $dt1->format("Y-m-d H:i:s");
						$logs[1]->save();
						$updated = true;
						$attendantLog = $logs[1];
						//return [false ,"上午已经签到，无法同时在上午提交外勤"];
					}else{
                        $logData['target_address'] = $logData["location"];
						$logData['state'] = AttendantLog::STATE_NOMAL;
						$logData['amount'] = 0.5;
					}
					
				}
	
			}
			//updated用于部分更新日志操作，此时不需要再创建日志
			if(!$updated){
				$attendantLog = $attendant->logs()->create($logData);
			}
			
			if( $attendantLog){//&& $data['state']==1 && $logs[0]->state==1
				
				$attendant->total += $attendantLog->amount ;
	
				if($logCount==1 && $attendant->total==1 ){
					// 如果考勤两次 且 最终考勤分小于1 则认为缺勤
					$attendant->state = 1;
				}else{
					$attendant->state = 2;
				}
				if($logCount==2){
					$attendant->count==2;
				}else{
					$attendant->count++;
				}
				$attendant->save();
				
			}
		
		    \DB::commit();
		    return [true, $attendant];
		    
		} catch (\Exception $e) {
		    \DB::rollback();
		    return [false , $e->getMessage()];
		}
		
		
	}
	
	public function getDepartment(){
		$did = $this->departmentId();
		return Department::findOrFail($did);
	}
	
	public function departmentId(){
	    return $this->department;
		$dep = explode(",", $this->department);
		foreach ($dep as $d){
			if($d!=1){
				return $d;
			}
		}
		return 1;
	}
	
	
	
 	/* public  function save(array $attributes = [],$sync=true){
 	 
	 //看看id是否存在
	 if($sync){
	 	$wxdata = [
	 	"userid"=> $this->userid,
	 	"name"=> $this->name,
	 	"department"=> explode(',',$this->department), //implode(',' , $this->department)
	 	"position"=> $this->position,
	 	"mobile"=> $this->mobile,
	 	"gender"=> $this->gender,
	 	"email"=> $this->email,
	 	"weixinid"=> $this->weixinid,
	 	//"avatar_mediaid"=> "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
	 	];
	 	if($this->id){
	 		//更新
	 		$wxdata["enable"] = $this->enable;
	 		$res = $this->wxuser->update($wxdata);
	 	}else{
	 		//新增
	 		$res = $this->wxuser->create($wxdata);
	 	}
	 	if( $res["errmsg"] && ($res["errmsg"]=="created" || $res["errmsg"]=="updated" )){
	 		parent::save();
	 	}	
	 }else{
	 	parent::save();
	 }
	 
	
	} */
	
	public  function synchFromRemote(){
		//$this->truncate();
		$users = $this->wxuser->lists(1,1,0);
        //dd($users["userlist"]);
		//dd($users);
		if($users["userlist"]){
			foreach ($users["userlist"] as $vo ){
				//首先检查id是否存在，不存在生成
				/* $dep = Department::find($gvo["id"]);
				 if($dep){
				$dep->name=
				}else{
			
				} */

			$deps = array_filter($vo["department"]);
            //dd($vo["department"]);
			static::create([
				"userid"=> $vo["userid"],
				"name"=> $vo["name"],
				"dep"=> $deps[count($deps)-1],
				"department"=> implode(',',$deps), //implode(',' , $this->department)
				"position"=> isset($vo["position"])?$vo["position"]:"",
				"mobile"=> isset($vo["mobile"])?$vo["mobile"]:"",
				"gender"=> $vo["gender"],
				"email"=> isset($vo["email"])?$vo["email"]:"",
				"weixinid"=> isset($vo["weixinid"])?$vo["weixinid"]:"",
				//"avatar_mediaid"=> "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
				]);
			}	
		}
		
	}
	
	const LEVEL_EMPLOYEE = 1;
	const LEVEL_VICE_CHARGE = 2;
	const LEVEL_CHARGE = 2;
	
	public function getLevel(){
		//获取员工级别
		switch ($this->position){
			case "院长":
			case "分管院长";
			case "人力资源部部长":
			case "部门正职":
					return static::LEVEL_CHARGE;
			case "部门副职":
				   return static::LEVEL_VICE_CHARGE;
			case "普通员工":
					return static::LEVEL_EMPLOYEE;
			default:
					return 0;
		}

	}
	
	public function getVocationRule($days){
		//判断员工等级
		if( $this->getLevel()==1 ){
			
		}
	}
	
	public  function delete(){
		$res = $this->wxuser->delete($this->userid);
		if( $res["errmsg"] && $res["errmsg"]=="deleted" ){
			parent::delete();
		}else{
            throw new \ErrorException( $res["errmsg"] );
        }
	}
	
}
