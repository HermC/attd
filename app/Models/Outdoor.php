<?php

namespace App\Models;

class Outdoor  extends Model
{
  
	protected $table = 'outdoor';
    protected $appends = ['vtime','vftime'];

    public function getVtimeAttribute(){
        $hour = $this->created_at->hour;
        $when = $hour>12?"下午":"上午";
        return $when.$this->created_at->format('H:i');
    }

    public function getVftimeAttribute(){
        return $this->created_at->format('m-d');
    }
}
