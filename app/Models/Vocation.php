<?php

namespace App\Models;


use Stoneworld\Wechat\Http;
use Stoneworld\Wechat\AccessToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Stoneworld\Wechat\Broadcast;
use Stoneworld\Wechat\Messages\NewsItem;
use Stoneworld\Wechat\Message;
class Vocation  extends Model
{
  
	protected $table = 'vocation';
	
	protected $appends = ['start','vstate','vresult'];

    const R_REJECTED =2;
    const R_APPROVED =1;
    const STATUS_WAIT = 1;
    const STATUS_PROCESS=2;
    const STATUS_COMPLETE=3;

	public static  function  remote(){
		Cache::forget('holiday');
		$config = Cache::get('holiday',function(){
			$appId  = config("ent.appId");
	    	$secret = config("ent.secret");
			$http = new Http(new AccessToken($appId, $secret));
			$year = date("Y");
			 $response = $http->get( "http://www.easybots.cn/api/holiday.php?m={$year}01,{$year}02,{$year}03,{$year}04,{$year}05,{$year}06,{$year}07,{$year}08,{$year}09,{$year}10,{$year}11,{$year}12");
			 return $response;
		});
		return $config;
		
	}
	
	public static function todayIsHoliday($userid=0,$rule=null,$date=""){
		$holidays =   static::remote();
		$dt = empty($date)?Carbon::now():Carbon::createFromFormat("Y-m-d H:i:s", $date);

		$key = $dt->format("Ym"); //date("Ym");
		$day = $dt->day;//date("d");
		$week = $dt->weekOfMonth;//date("w");
		if( !isset($holidays) ||  !isset($holidays[$key]) ) {
			return true;
		}
		$days = $holidays[$key];
		$res    = isset($days[$day])?$days[$day]:3;
		switch ($res){
			
			case 1:
				//如果是星期日星期日 检测一下是否是法定假日
			case 2:
				//非周六周日 缺是法定假日
				return true;
			case 3:
				//看是否是工作日
				if($rule){
					$wordays = explode(",", $rule->workday);
					if(!in_array($week,	$wordays)){
						return true;
					}	
				}
				//再看看自己是否请假了
				$key2 = $dt->format("Y-m-d 00:00:00");//date("Y-m-d");
				$hour = $dt->hour;
				$type = ($hour<12)?1:2;

				$vd =   VocationDays::where("employee_id",$userid)->where("day",$key2)->where("type",$type)->first();

				if(!$vd){
					return false;
				}else{
                    if( $vd->vocation->result == Vocation::R_APPROVED ){
                        return $vd;
                    }
                    return false;
				}
		}
		/* if(in_array($day, $holidays[$key])){
			return true;
		}else{
			return false;
		} */
		
	}
	
	public static function getHolidaysOfMonth($month){
		$holidays =   static::remote();
		$key = Carbon::now()->month($month)->format('Ym');

		if( isset($holidays) &&  isset($holidays[$key]) ) {
			return $holidays[$key];
		}else{
			return false;
		}

	}
	
	public static   function applyVocation($data,$sType,$eType,$user){
		
		\DB::beginTransaction();
		try{
			$vocation = static::create($data);
			$dates = [];
			$st = Carbon::createFromFormat( "Y-m-d" , $data["start_time"] );
			$et = Carbon::createFromFormat( "Y-m-d" , $data["end_time"] );
			//$dates[] = $data["start_time"];
			$total = 0;
			for ($i=0 ; $st->lte($et) ; $i++){
				if( $i%2==0 ){
					$type = $sType;
				}else{
					$type = ($sType==1)?2:1;
				}

				$vocation->vdays()->create([
						"vacation_rule_id"=>$data["vocation_rule_id"],
						"depart_id"=>$data["depart_id"],
						"depart_name"=>$data["depart_name"],
						"employee_id"=>$data["employee_id"],
						"employee_name"=>$data["employee_name"],
						"day"=>$st->format("Y-m-d"),
						"type"=>$type,
				]);
                $dates[] = $st->format("Y-m-d")."-".$type;

				$total += 0.5;
				//每次是下午就要累加 如果是半天也要累加
				if( $type==2|| $sType==$eType  || ($st->eq($et)&&($eType==1)) ){

					$st->addDay();
				}
			}
			
			$vocation->datelist = implode(',',$dates);
			$vocation->days =  $total;
			
			$vocation->save();
			
			//根据请假规则id 获取所有审核规则信息
			$rule = $vocation->getAuditRule($user);
			
			if(!isset($rule)){
				throw new \Exception("无规则可用");
			}
			$vocation->audit_id = $rule->audit_id;
			$vocation->state = 2;
			$vocation->save();
			//同时生成第一条审核记录
			$vocation->applyAudit();
				
			\DB::commit();
		return [true,$vocation];
		
		} catch (\Exception $e) {
			\DB::rollback();
			return [false , $e->getMessage() ];
		}

	}
	
	public function getVstateAttribute(){
		switch($this->state){
			case 1:
				return "等待审核";
			case 2:
				return "批准进行中";
			case 3:
				return "审核完成";
		}
	}
	public function getVresultAttribute(){
		switch($this->result){
			case 1:
				return "批准";
			case 2:
				if($this->state==1){
					return "等待审批";
				}
                if($this->state==2){
                    return "审批中";
                }
                if($this->state==3){
                    return "审批完成";
                }

		}
	}
	public function getStartAttribute(){
		return Carbon::createFromFormat("Y-m-d H:i:s", $this->start_time)->format('Y-m-d');
	}
	
	/**
	 * 根据当前实例用户，获取请假审核规则
	 * @return unknown|NULL
	 */
	public function getAuditRule($employee){
		
		//获取本条请假规则
		$leve = $employee->getLevel();

		//获取某一个 请假规则下 符合当前员工身份的审核规则  
		//TODO 注意必须要设置一个默认规则，可以在没找到规则的情况下给所有数据用
		$rule_audits = VocationRuleAudit::where( "rule_id" , $this->vocation_rule_id )->whereRaw("find_in_set('$leve',target)")->get();
		$rule = null;
		foreach ($rule_audits as $ra){
			if( $ra->mindays<=$this->days && ( $ra->maxdays==0 || $this->days<=$ra->maxdays ) ){
				//只要找到符合规则的，就返回当前这条
				return $ra;
			}
		}
		
		if(!isset($rule)){
			//设置一个默认的规则
		}
		return $rule;
	}
	
	public function applyAudit(){
		//实现判断当前进行到哪里了。

		$alreadyAuditPeople= $this->audit;
		if(count($alreadyAuditPeople)==0){
			$alreadyAuditPeople= [];
		}else{
			$alreadyAuditPeople=$alreadyAuditPeople->pluck("audit_user")->all();
		}
		$auditRule = $this->auditRule;
		//$auditors = collect(explode(",", $auditRule->people));
        $auditors = collect( $auditRule->getAuditors($this->depart_id)->keys() );
		$diff = $auditors->diff($alreadyAuditPeople)->values()->all();
		$broadcast = new Broadcast( config("ent.appId"), config("ent.secret"));

		if( count($diff)>0 ){
            $au = $this->audit()->create([
                "employee_id"=>$this->employee_id,
                "department_id"=>$this->depart_id,
                "audit_user"=>$diff[0],
                "vocation_rule_id"=>$this->vocation_rule_id,
                "audit_rule_id"=>$this->audit_id
            ]);
			return ["wait",$au];

		}else{
            //TODO 完成了审核 发送消息通知 请假人
            /*$newsItem = new NewsItem();
            $newsItem->title ='审核已完成';
            $newsItem->description = "您的审核已经完成";
            $newsItem->pic_url = 'http://iph.href.lu/600x400';
            $newsItem->url =  route("vocations");
            $message = Message::make('news')->item($newsItem);
            $broadcast->fromAgentId(config('ent.attendant_chat'))->send($message)->to($this->employee->userid);*/
            return ["done",null];
        }

	}
	
	/* public function getAuditRule($days, $vocation_rule_id,  $leve){
		//获取本条请假规则
		//$leve = $employee->getLevel();
		//获取某一个 请假规则下 符合当前员工身份的审核规则
		//TODO 注意必须要设置一个默认规则，可以在没找到规则的情况下给所有数据用
		$rule_audits = VocationRuleAudit::where( "rule_id" , $vocation_rule_id )->whereRaw("find_in_set('$leve',target)")->get();
		$rule = null;
	
		foreach ($rule_audits as $ra){
			if( $ra->mindays<= $days && ($ra->maxdays==0 || $days<=$ra->maxdays ) ){
				//只要找到符合规则的，就返回当前这条
				$rule = $ra;
			}
		}
	
		if(!isset($rule)){
			//设置一个默认的规则
		}
		return $rule;
	} */
	
	public function vdays()
	{
		return $this->hasMany(VocationDays::class,'vacation_id','id');
	}
	
	public function audit()
	{
		return $this->hasMany(VocationAudit::class,'vocation_id','id');
	}
	
	public function employee()
	{
		return $this->belongsTo(Employee::class,'employee_id','id');
	}
	
	public function rule()
	{
		return $this->belongsTo(VocationRule::class,'vocation_rule_id','id');
	}
	
	public function auditRule()
	{
		return $this->belongsTo(AuditRule::class,'audit_id','id');
	}
	
	
	
}
