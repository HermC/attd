<?php

namespace App\Models;

class VocationRule  extends Model
{
  
	protected $table = 'vocation_rule';
	
	public function rules()
	{
		return $this->hasMany(VocationRuleAudit::class,'rule_id','id');
	}
	
}
