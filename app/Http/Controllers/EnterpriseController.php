<?php
/*
 * to-do:
 * 判断活动状态 
 * */
namespace App\Http\Controllers;
use App\Models\Outdoor;
use Stoneworld\Wechat\User;
use Stoneworld\Wechat\Group;
use App\Models\Department;
use App\Models\Attendant;
use App\Models\AttendantRule;
use App\Models\Employee;
use Carbon\Carbon;
use App\Models\Vocation;
use App\Models\VocationRule;
use Illuminate\Support\Facades\Storage;
use App\Models\AttendantLog;
use App\Models\VocationAudit;
use App\Models\Statics;

class EnterpriseController extends BaseController
{

	public function __construct(){
		parent::__construct();
		//生成缓存  常见数据
	
	}
	
	
	
	public  function statics(){
		$user =  session('wechat.oauth_user');
		$dep = $user->getDepartment();
		//获取本部门所有统计数据
		$type = isset($this->data["week"])&&$this->data["week"]?true:false;
		$report = Statics::leaderReport($dep->id,$type);
		return response()->view("enterprise.statics",["data"=>$report,"type"=>$type]); //,["user"=>$tester]
		
	}

    /**
     * @应用首页
     * @return \Illuminate\Http\Response
     */
    public  function index(){
        return response()->view("enterprise.index"); //,["user"=>$tester]
    }

    /**
     * @route attendants  考勤列表
     * @return \Illuminate\Http\Response
     */
    public  function attendants(){
        return response()->view("enterprise.attendants");
    }

    /**
     * @route api_attendants  考勤列表api
     * @return mixed
     */
    public  function apiAttendants(){
        /* $ret = Vocation::remote(); */
        $user =  session('wechat.oauth_user');
        $keyword = isset($this->data['q'])?$this->data['q']:"";
        $start = isset($this->data['start'])?$this->data['start']:"";
        $end = isset($this->data['end'])?$this->data['end']:"";
        $rc = Attendant::where('employee_id',$user->id)->orderBy('created_at','desc');
        if(!empty($start)){
            $rc = $rc->where('created_at',">",Carbon::createFromFormat("Y-m-d", $start)->format('Y-m-d H:i:s') );
        }
        if(!empty($end)){
            $rc = $rc->where('created_at',"<",Carbon::createFromFormat("Y-m-d", $end)->endOfDay()->format('Y-m-d H:i:s') );
        }
        $rc = $rc->with("rule")->with("logs")->paginate(10);
        return $this->json( ["success"] , [ 'attendants'=>$rc ] );
    }

    /**
     * 签到入口
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function attendant(){

        /* $userid = $this->data["userid"];
        $gps = $this->data["gps"]; */
        //这里要么有个页面，通过地图获取用户所在位置gps信息，要么通过微信消息获取
        //$userid = "shenloveliu";
        //$user = Employee::where("userid",$userid)->first();


        $user =  session('wechat.oauth_user');
        if(isset($this->data["rule_id"])){
            $validRule = AttendantRule::find($this->data["rule_id"]);
        }


        if(isset($this->data["gps"])){
            $userid = $user->userid;
            $gps = explode('|' , $this->data["gps"]);//["118.744448","32.010906"];
            $validRule  = $user->getRulesByGPS($gps);
        }

        if(!isset($validRule)){
            return 	redirect()->route("msg",["type"=>"error","title"=>"规则不存在","content"=>"不存在签到规则，进入有效签到范围内才可签到。如遇问题请联系管理员"]);
        }

        $res = Vocation::todayIsHoliday($user->id,$validRule);

        if($res){
            return 	redirect()->route("msg",["type"=>"error","title"=>"休假日","content"=>"目前处在休假时间，无需签到。如遇问题请联系管理员"]);
        }

        $dt1 = Carbon::now();
        $attendant =  $user->attendantOfDate($dt1);
        $attendantLogs = isset($attendant)?$attendant->logs:null;

        //或者根据rule id 直接获取
        //$validRule = AttendantRule::find($ruleId);

        $in = null;
        $out = null;
        if($attendantLogs){
            if( isset( $attendantLogs[0] ) )
                $in = $attendantLogs[0];
            if( isset( $attendantLogs[1] ) )
                $out = $attendantLogs[1];
        }
        return response()->view("enterprise.sigin", ["rule"=>$validRule,"in"=>$in,"out"=>$out ,"user"=>$user]); //,["user"=>$tester]

    }

    /**
     * 提交签到
     * @return mixed
     */
    public function postAttendant(){

        //员工签到
        // 先获取员工信息(微信带过来或者auth认证)
        //$userid = $this->data["userid"];
        $gps = $this->data["gps"];

        $user =  session('wechat.oauth_user');
        //$user = Employee::where("userid",$userid)->first();
        if(!$user){
            return $this->json(["error","error","用户不存在"]);
        };
        //用户请假的时候 在这里要自动生成两条缺席记录
        //判断是否在这天有请假申请，如果有 返回 已请假，无须签到

        //看一下今天是否是法定假日 如果是，不用签到 生成休息记录
        //看一下今天是否请假了


        //$gps = $this->data;
        $validRule  = $user->getRulesByGPS($gps);

        if(!$validRule){
            return $this->json(["error","error","无可用签到信息"]);
        }

        list($status,$ret) = $user->attendant($validRule,$gps);

        if($status){
            if($ret){
                $msg = "签到成功";
            }else{
                $msg = "签退成功";
            }
            return $this->json(["success","success",$msg]);
        }else{
            return $this->json(["error","error", $ret ]);
        }

        // 查找所在部门
        // 找到部门所有规则（缓存中）
        // 检查是否存在当天该规则的请假记录，如果请假流程已经通过，自动生成考勤记录
        // 根据考勤规则检查考勤合法：1 检查距离是否在范围，不在范围返回错误
        // 根据设置的时间，考勤员工，并生成相应的记录

        //return $this->response("enterprise.sigin",[]);
    }


    /**
     * 外勤记录
     * @return \Illuminate\Http\Response
     */
    public  function outdoors(){
        $user =  session('wechat.oauth_user');

        return response()->view("enterprise.outdoors", ["user"=>$user] );
    }
    public  function apiOutdoors(){
        /* $ret = Vocation::remote(); */
        $user =  session('wechat.oauth_user');
        $keyword = isset($this->data['q'])?$this->data['q']:"";
        $start = isset($this->data['start'])?$this->data['start']:"";
        $end = isset($this->data['end'])?$this->data['end']:"";
        $rc = Outdoor::where('employee_id',$user->id)->orderBy('created_at','desc');
        if(!empty($start)){
            $rc = $rc->where('created_at',">",Carbon::createFromFormat("Y-m-d", $start)->format('Y-m-d H:i:s') );
        }
        if(!empty($end)){
            $rc = $rc->where('created_at',"<",Carbon::createFromFormat("Y-m-d", $end)->endOfDay()->format('Y-m-d H:i:s') );
        }
        $rc = $rc->paginate(10);
        return $this->json( ["success"] , [ 'attendants'=>$rc ] );
    }
    //外勤
    public  function outdoor(){

        $user =  session('wechat.oauth_user');
        $dt1 = Carbon::now();
        /*$attendant =  $user->attendantOfDate($dt1);
        if($attendant&&count($attendant->logs)==2){
            return 	redirect()->route("msg",["type"=>"error","title"=>"无法提交外勤","content"=>"今天已经考勤已经结束，无法继续提交外勤。如遇问题请联系管理员"]);
        }*/
        return response()->view("enterprise.outdoor", ["user"=>$user] );
    }

    public  function postOutdoor(){

        //$userid = $this->data["userid"];
        $gps = $this->data["gps"];
        $location = $this->data["location"];
        $memo = $this->data["memo"];

        //$user = Employee::where("userid",$userid)->first();
        $user =  session('wechat.oauth_user');
        if(!$user){
            return $this->json(["error","error","用户不存在"]);
        };

        list($status,$ret) = $user->attendant(null,$gps,$location,$memo);

        if($status){
            if($ret->type==1){
                $msg = "签到成功";
            }else{
                $msg = "签退成功";
            }
            return $this->json(["success","success",$msg]);
        }else{
            return $this->json(["error","error", $ret ]);
        }

    }
	public  function vocation(){
		$rules = VocationRule::select('id','title','proof')->get();
		return response()->view("enterprise.vocation",["rules"=>$rules]); 
	}
	
	public  function apiVocations(){
		/* $ret = Vocation::remote(); */
		$user =  session('wechat.oauth_user');
		$keyword = isset($this->data['q'])?$this->data['q']:"";
		$vocations = Vocation::where('employee_id',$user->id)->orderBy('created_at','desc')->with("rule")->with("audit")->paginate(10);
		return $this->json( ["success"] , [ 'vocations'=>$vocations ] );
	}
	
	public  function apiVocationAudit(){
		$user =  session('wechat.oauth_user');
		$audits = VocationAudit::where("audit_user",$user->id)->orderBy('created_at','desc')->with("vrule")->with("vocation")->paginate(10);
		return $this->json( ["success"] , [ 'audits'=>$audits ] );
	}
	
	public function vocation_audits(){
		return response()->view("enterprise.vocation_audits");
	}

    /**
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function vocationAudit($id){
		$user =  session('wechat.oauth_user');
        $depart_id = $user->departmentId();
		$audit = VocationAudit::where("id",$id)->with("vocation")->with("vrule")->with("arule")->first();
		/* if($audit->audit_user!=$user->id){
			return 	redirect()->route("msg",["type"=>"error","title"=>"无权审核","content"=>"您无权审核此请假申请，遇到问题请联系管理员"]);
		} */
        list($year,$issue,$sick) = $user->getVocationAccount();
		$audits = $audit->vocation->audit->keyBy("audit_user");
		return response()->view("enterprise.vocation_audit",  ["audit"=>$audit,"audits"=>$audits,"histories"=>[$year,$issue,$sick],'depart_id'=>$depart_id ] );
	}
	public  function postVocationAudit($id){
		$user =  session('wechat.oauth_user');
		$status = $this->data["status"];
		$memo = $this->data["memo"];
		$v = VocationAudit::find($id);
		if($v->audit_user!=$user->id){
			return 	$this->json(["error","error","您无权审核此请假申请，遇到问题请联系管理员"]);
		}
		if($v->result!=0){
			return 		$this->json(["error","error","您已审核过此申请，遇到问题请联系管理员"]);
		}
		list($result,$flag) = $v->makeAudit($status,$memo);
        return $this->json(["success"],["flag"=>$flag]);

	}
	public  function vocations(){
		return response()->view("enterprise.vocations");
	}
	
	public  function postVocation(){
		
		$user =  session('wechat.oauth_user');
		$data = [];
		$data["employee_name"] = $user->name;
		$data["employee_id"] = $user->id;
		$data["depart_name"] =$user->getDepartment()->name;
		$data["depart_id"] = $user->departmentId();
		$data["audit_id"] = 0; //先设置为0 这里要根据天数判断审核规则
		$data["vocation_rule_id"] = $this->data["vocation_rule_id"];
		$data["start_time"] = $this->data["start_time"];
		$sType = $this->data["sType"];
		$data["end_time"] = $this->data["end_time"];
		$eType = $this->data["eType"];
		$data["memo"] = $this->data["memo"];
		//$data["type"] = $this->data["type"];

		if( isset( $this->data['attach'] ) ){
			$file = basename( $this->data['attach']);
			$newfile = $user->id."/".$file ;
			Storage::move(  "temp/{$file}", $newfile  );
			$data["attach"] = '/'.$newfile ;
		}
		list($flag,$msg) = Vocation::applyVocation($data, $sType, $eType , $user );
		if($flag){
			return $this->json(["success","success","提交成功"]);
		}else{
			return $this->json(["error","error",$msg]);
		}
		
	}

	public function rules(){
	
		$user =  session('wechat.oauth_user');
		$dt1 = Carbon::now();
		$attendant =  $user->attendantOfDate($dt1);
		$attendantLogs = isset($attendant)?$attendant->logs:[];
		$count  = count($attendantLogs);
		if($count==1&&$attendantLogs[0]->type!=3){
			
			return response()->view("enterprise.sigin", ["rule"=>$attendantLogs[0]->rule,"in"=>$attendantLogs[0],"out"=>null ,"user"=>$user]); //,["user"=>$tester]
			
		}
		if($count==2){
			return redirect()->route("msg",["type"=>"success","title"=>"签到已完成","content"=>"今天签到已完成，如有问题请联系管理员"]);
		}
		//看一下今天是否是法定假日 如果是，不用签到 生成休息记录
		$res = Vocation::todayIsHoliday();
		if($res){
			return redirect()->route("msg",["type"=>"success","title"=>"无须签到","content"=>"今天是非工作日（法定假日 非工作日 个人请假），无须签到"]);
		}
		//看一下今天是否请假了
		//获取规则列表

		$depid = $user->departmentId();

		//需要缓存
		$rules = AttendantRule::whereRaw("find_in_set('{$depid}',deps)")->get();
		return response()->view("enterprise.rules",["rules"=>$rules]); //
	
	}
	
}
