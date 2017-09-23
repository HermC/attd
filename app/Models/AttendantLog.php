<?php

namespace App\Models;

class AttendantLog  extends Model
{
  
	const TYPE_SIGIN = 1;
	const TYPE_SIGOUT = 2;
	const TYPE_OUTDOOR = 3;
	const TYPE_HOLIDAY = 4;
	const TYPE_VOCATION = 5;
	
	const STATE_NOMAL = 1;
	const STATE_LATE = 2;
	const STATE_EARLY = 3;
	const STATE_ABCENT = 5;
	
	protected $table = 'attendance_log';

	
	protected $appends = ['vtime','vftime','vstate','vresult'];
	
	public function getVtimeAttribute(){
		return $this->created_at->format('H:i:s');
	}
	
	public function getVftimeAttribute(){
		return $this->created_at->format('m-d');
	}
	
	public function getVstateAttribute(){
		switch($this->type){
			case 1:
				return "签到";
			case 2:
				return "签退";
			case 3:
				return "外勤";
			case 4:
				return "休息";
			case 5:
				return "请假";
		}
	}
	
	public function getVresultAttribute(){
			switch($this->state){
					case 1:
						return "正常";
					case 2:
						return "迟到";
					case 3:
						return "早退";
					case 4:
						return "外勤";
					case 5:
						return "缺勤";
				}
	}
	
	public function outdoor()
	{
		return $this->belongsTo(Outdoor::class,'outdoor_id','id');
	}
	
	public function vday()
	{
		return $this->belongsTo(VocationDays::class,'vocation_id','id');
	}
	
	public function rule()
	{
		return $this->belongsTo(AttendantRule::class,'rule_id','id');
	}
	
	public function employee()
	{
		return $this->belongsTo(Employee::class,'employee_id','id');
	}
	
	public function department()
	{
		return $this->belongsTo(Department::class,'depart_id','id');
	}
	
	public function attendant()
	{
		return $this->belongsTo(Attendant::class,'attendant_id','id');
	}
	
	
	public  function getStateDesc(){
		switch ($this->state){
			case 1:
				return "正常";
		   case 2:
				return "迟到";
			case 3:
			    return "早退";
			 case 4:
			    	return "外勤";
			   case 5:
			    		return "缺勤";
		}
	}
	
}
