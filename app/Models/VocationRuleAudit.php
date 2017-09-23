<?php

namespace App\Models;

class VocationRuleAudit  extends Model
{
  
	protected $table = 'vocation_rule_audit';
	
	public function rule()
	{
		return $this->belongsTo(VocationRule::class,'rule_id','id');
	}
	
	public function audit()
	{
		return $this->belongsTo(AuditRule::class,'audit_id','id');
	}


}
