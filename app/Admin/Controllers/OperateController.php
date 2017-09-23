<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Response;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Models\Site;
use App\Models\Fund;
use App\Services\MessageService;
use App\Models\ActivityBill;
use Illuminate\Support\Facades\Cache;
use App\Models\Company;
use App\Models\Config;
use App\Models\User;
use App\Models\ActivitySponsorConfig;
use App\Models\ActivitySponsorLog;
use App\Models\ActivityPraise;
use App\Models\ClassicCase;
use App\Models\Vocation;
use App\Models\AttendantLog;
use Encore\Admin\Facades\Admin;

class OperateController extends Controller
{
   
	
    public function operate(Response $response){
    	
    	$op = $this->data["op"];
    	 
    	switch($op){
    		case 'modifyNomal':
    			$rr = isset($this->data["rr"])?$this->data["rr"]:"";

    			$log = AttendantLog::find($this->data["id"]);
    			$attendant =$log->attendant;
                $rule = $log->rule;
                if($rule){
                    if($log->type==AttendantLog::TYPE_SIGIN){
                        $log->created_at = Carbon::createFromFormat("H:i", $rule->sigin_time );
                    }
                    if($log->type==AttendantLog::TYPE_SIGOUT){
                        $log->created_at = Carbon::createFromFormat("H:i", $rule->sigout_time );
                    }
                }
    			$log->state = 1;
    			$log->modify_log = $rr;

    			if($log->amount ==0){
    				$attendant->total += 0.5;
    				$log->amount = 0.5;
    				if( $attendant->total ==1 ){
    					$attendant->state = 1;
    				}
    			}

    			$log->admin_id = Admin::user()->id;
    			$log->save();
    			//TODO 这里要更新考勤报告字段
    			$attendant->save();
    			return response()->json([
    					'status'  => true,
    					'message' =>"操作成功",
    					]);
    			break;
    		case 'auditVocation':
    			$status = $this->data["status"];
    			$rr = isset($this->data["rr"])?$this->data["rr"]:"";
    			$v = Vocation::find($this->data["id"]);
    			$au = $v->audit()->create([
    					"audit_user"=>1,
    					"result"=>$status,
    					"memo"=>$rr
    			]);
    			$v->memo = $rr;
    			$v->audit_id = $au->id;
    			$v->result=$status;
    			$v->state=3;
    			$v->save();
    			return response()->json([
    					'status'  => true,
    					'message' =>"操作成功",
    			]);
	
    		default:
    			return $this->response(['error','error','无可用操作']);
    	}
    	
    	return response()->json([
    				'status'  => false,
    				'message' =>"操作失败",
    				]);
    	
    }
    
}
