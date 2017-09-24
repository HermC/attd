<?php

namespace App\Models;
use Carbon\Carbon;
class NotifyLog  extends Model
{
	
	protected $table = 'notify_log';
	
	public static function log($targetid,$type){
		return static::create([
			"type"=>$type,
			"bid"=>$targetid
		]);
	}
	
	public static function isNotifiedToday($targetid,$type){
		$log = static::where("bid",$targetid)->where( "type" ,$type )->first();
		//$log = static::where("bid",$targetid)->whereBetween( "created_at" ,$span )->first();
		return isset($log)?true:false;
	}
	
}
