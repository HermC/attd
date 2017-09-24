<?php

namespace App\Models;

class VocationDays  extends Model
{
  
	protected $table = 'vocation_days';

    public function vocation(){
        return $this->belongsTo(Vocation::class,'vacation_id','id');
    }
	
}
