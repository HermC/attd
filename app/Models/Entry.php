<?php

namespace App\Models;
use Dyrynda\Database\Support\GeneratesUuid;
use Carbon\Carbon;
class Entry  extends Model
{
   

	use GeneratesUuid;
	
	/**
	 * 车辆已经入场地
	 */
	const  STATE_ENTRY =1;
	
	/**
	 * 车辆已离开场地
	 */
	const  STATE_LEAVE =1;
	
	protected $table = 'entries';
	
	
	/* public function __construct(array $attributes = [])
	{
		$connection = config('admin.database.connection') ?: config('database.default');
	
		$this->setConnection($connection);
	
		$this->setTable(config('admin.database.menu_table'));
	
		parent::__construct($attributes);
	} */
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /* protected $fillable = [
       
    ]; */

    protected $guarded = [];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    
    protected $appends = [];
     
    protected static function boot()
    {    	
    	/* 
    	if(session('user_location')){    		
    		static::addGlobalScope(new CityScope);
    	} */
    	parent::boot();
    }
    
    public function black()
    {
    	return $this->hasOne(Black::class,'car_no','car_no');
    }
    
    public function carType()
    { 
    	return $this->belongsTo(CarType::class,'car_type','id');
    }
    
    public function settle()
    {
    	return $this->hasOne(EntrySettle::class,'entry_id','id');
    }
    
    public function exhibition()
    {
    	return $this->belongsTo(Exhibition::class,'exhibition_id','id');
    }
    
    public function logs()
    {
    	return $this->hasMany(EntryLog::class,'entry_id','id');
    }
    
     public static  function getEntryTotal($exId){
    	//获取进口金钱统计数据  所有工本费加押金
     	$now =Carbon::now()->toDateTimeString();
     	$startOfDay = Carbon::now("Asia/Shanghai")->startOfDay()->toDateTimeString();
     	$incEntries = Entry::where("exhibition_id",$exId)->whereBetween("created_at",[$startOfDay,$now])->get();//假设当天进场的 当天又出场
     	$costCount = 0;
     	$pledgeCount = 0;
     	$notOffCount = 0;
     	$offCount = 0;
     	$extraMoney = 0;
     	$gbIncoming = 0;
     	$payBack = 0;
     	$incEntries->each( function($value)use(&$costCount,&$pledgeCount,&$notOffCount,&$offCount,&$extraMoney,&$payBack,&$gbIncoming){
     		$costCount +=  $value->cost;
     		$pledgeCount +=  $value->pledge;
     		if($value->state==1)	$notOffCount++;
     		if($value->state==2)	{
     			$offCount++;
     			
     			if($value->settle->is_force){
     				$extraMoney += $value->settle->force;
     			}else{
     				$extraMoney += $value->extra;
     				$gbIncoming += $value->cost;
     			}
     			
     			$payBack += $value->pledge;
     		}
     	} );
     	return [$costCount,$pledgeCount,$notOffCount,$offCount,$extraMoney,$payBack,$gbIncoming];
    }
    

     /**
     * 获取当前是否开始
     * @author rayo
     */
    /*public function isActivityStarted(){
    	$startTime = strtotime($this->start_time);
    	$now = Carbon::now();
    	$startTime = new Carbon($this->start_time);
    	$endTime = new Carbon($this->end_time);
    	if($now->lt($startTime)){
    		return ["result"=>false,'msg'=>"活动还未开始",'progress'=>"0%" ];
    	}elseif($now->lt($endTime)){
    		return ["result"=>true,'msg'=>"活动已开始 {$startTime->diffInDays($now,false)} 天".($this->status!==2?"<span style='color:red'>[未审核]</span>":"") ,'progress'=>$this->getProgress() ];
    	}elseif($now->gt($endTime)){
    		return ["result"=>false,'msg'=>"活动已结束",'progress'=>"100%" ];
    	}
    	// return Carbon::createFromTimeStamp($this->expire)->toDateTimeString();
    	// return 'PD'.date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
    } */
  
    
}
