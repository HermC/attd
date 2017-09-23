<?php

namespace App\Models;

use Stoneworld\Wechat\Broadcast;
use Stoneworld\Wechat\Message;
use Stoneworld\Wechat\Messages\NewsItem;

class VocationAudit  extends Model
{
  
	protected $table = 'vocation_audit';

	protected $appends = ['astate','url'];
	
	public function getUrlAttribute(){
		return route("vocation_audit",["id"=>$this->id]);
	}
	
	public function getAstateAttribute(){
		switch ($this->result){
			case 0:
				return "等待审核";
			case 1 :
				return "通过审核";
			case 2:
				return "驳回申请";
		}
	}

	public function makeAudit($status,$memo){

        $this->update([
            "result"=>$status,
            "memo"=>$memo
        ]);

        $vocation = $this->vocation;
        list($res,$ad) = $vocation->applyAudit();
        $broadcast = new Broadcast( config("ent.appId"), config("ent.secret"));
        if($res=="done"||$status==2){
            //完成申请 或者请求被拒
            //检查是否有下一个审核人员，如果有，生成新的审核申请
            $vocation->state=3;
            $vocation->result=$status;
            if($status==2){
                $newsItem = new NewsItem();
                $newsItem->title ='您的审核被拒绝';
                $newsItem->description = "您的请假申请被拒绝，点击查看详情";
                $newsItem->pic_url = 'http://nqi.iselab.cn/img/audit.png';
                $newsItem->url =  route("vocation_audit",["id"=>$this->id]);
                $message = Message::make('news')->item($newsItem);
            }else{
                //TODO 发送消息给请假人
                $newsItem = new NewsItem();
                $newsItem->title ='审核通过';
                $newsItem->description = "您的请假申请已通过，点击查看详情";
                $newsItem->pic_url = 'http://nqi.iselab.cn/img/audit.png';
                $newsItem->url =  route("vocation_audit",["id"=>$this->id]);
                $message = Message::make('news')->item($newsItem);
            }
            $broadcast->fromAgentId(config('ent.attendant_chat'))->send($message)->to($this->auditor->userid);
            $vocation->save();
            return [$status==1?true:false,$status==1?"complete":"rejected"];
        }else{
            //TODO 发送消息，通知下一个审核的人
            $newsItem = new NewsItem();
            $newsItem->title ='有事假需要审核';
            $newsItem->description = "您有一条待审核的请假申请";
            $newsItem->pic_url = 'http://nqi.iselab.cn/img/audit.png';
            $newsItem->url =  route("vocation_audit",["id"=>$this->id]);
            $message = Message::make('news')->item($newsItem);
            $broadcast->fromAgentId(config('ent.attendant_chat'))->send($message)->to($this->auditor->userid);
            return [true,"next"];
        }



    }

	public function auditor()
	{
		return $this->belongsTo(Employee::class,'audit_user','id');
	}
	//hasOne
	public function vrule()
	{
		return $this->belongsTo(VocationRule::class,'vocation_rule_id','id');
	}
	
	public function arule()
	{
		return $this->belongsTo(AuditRule::class,'audit_rule_id','id');
	}
	
	public function vocation()
	{
		return $this->belongsTo(Vocation::class,'vocation_id','id');
	}
	
	public function getState(){
		switch ($this->result){
			case 0:
				return "等待审核";
			case 1 :
				return "通过审核";
			case 2:
				return "驳回申请";
		}
		
	}
	
}
