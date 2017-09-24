<?php

namespace App\Models;

class AuditRule  extends Model
{
  
	protected $table = 'audit_rule';

	public function getAuditors($departmentid){
        //$peoples = explode(",", $this->people);

        if( !empty($this->people) ){
            $auditors = explode(",",$this->people);
        }else{
            $parties = exploes("=>",$this->title);
            $auditors = [];
            foreach ($parties as $key=>$value){
                switch ($value){
                    case "部门审批人":
                        // 获取一个部门审批人
                        $department = Department::find($departmentid);
                        $charger = $department->charger;
                        if(isset($charger)){
                            $auditors[]=$charger->id;
                        }
                        break;
                    case "人力资源部长":
                        $hr =  Employee::where("position","like","%人力资源部长%")->first();
                        if(isset($hr)){
                            $auditors[]=$hr->id;
                        }
                        break;
                    case "分管院长":
                        $vice =  Employee::where("position","like","%分管院长%")->first();
                        if(isset($vice)){
                            $auditors[]=$vice->id;
                        }
                        break;
                    case "院长":
                        $cheif =  Employee::where("position","like","%院长%")->first();
                        if( isset($cheif) ){
                            $auditors[]=$cheif->id;
                        }
                        break;
                }
            }
        }

		$peoples = Employee::whereIn('id',$auditors)->pluck("name","id");

        $newPeoples = [];
        foreach ( $auditors as $key=>$ad ){
            if(isset($peoples[$ad])){
                $newPeoples[$ad] = $peoples[$ad];
            }
        }
		return collect($newPeoples);

	}
	
	const  AUDIT_DEAN = 4; //对应eployee里面的 部长
	const  AUDIT_VICE_DEAN = 3; //对应eployee里面的副院长
	const  AUDIT_HR_CHARGE = 2; //对应employee里面的人力资源部门主管

}
