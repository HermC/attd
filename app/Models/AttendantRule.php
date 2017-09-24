<?php

namespace App\Models;
use Carbon\Carbon;
class AttendantRule  extends Model
{
  
	protected $table = 'attendance_rules';

	protected $appends = ['url','distance'];
	
	public function getDistanceAttribute(){
		return 10000;
	}
	
	public function getUrlAttribute(){
	
		return route('singinOrSignout' ,['rule_id'=>$this->id]);
	
	}

	public static function getRulesByDep($id){
		/* \Cache::forget('attendant:rules:'.$id);
		return \Cache::get('attendant:rules:'.$id,function()use($id){
			return static::whereRaw("find_in_set('{$id}',deps)")->get();
		}); */
		return static::whereRaw("find_in_set('{$id}',deps)")->get();
	}

	//获取签到时间
	public  function getSiginTime(){
		$dt =  Carbon::createFromFormat('H:i', $this->sigin_time);
		return $dt;
		//\Carbon::createFromTime($hour, $minute, $second, $tz);
	}

	//获取签出时间
	public  function getSigoutTime(){
		$dt =  Carbon::createFromFormat('H:i', $this->sigout_time);
		return $dt;
		//\Carbon::createFromTime($hour, $minute, $second, $tz);
	}
	
}
