<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\AttendantRule;
use App\Models\Attendant;
use App\Models\Department;
use App\Models\Statics;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         // $schedule->command('inspire')
        //          ->hourly();
        // myzhou delete start
		//$schedule->call(function(){
		//ActivityService::endAcitvity();//检查所有活动 是否结束
		//})->everyTenMinutes();
    	// myzhou delete end
    	
    	
    	//上班前5分钟通知签到
    	 $schedule->call(function(){
    	 	Department::departNotifyScheduel(1);
    	})->everyMinute()
    	->between( '7:00' ,  '10:00'); 
    	
    	
    	//如何处理 下班班前通知
    	$schedule->call(function(){
    		Department::departNotifyScheduel(2);
    	})->everyMinute()
    	->between( '15:00' ,  '19:00');
    	
    	//每天6点 自动考勤
    	$schedule->call(function () {
    		Attendant::specialAttendant();
    	})->dailyAt('6:00');
    	
    	// 每天22点运行 补全签到
    	$schedule->call(function () {
    		Attendant::autoCheckAttendant();
    	})->dailyAt('22:00');
    	
    	// 每天23点运行 全天签到统计 
    	$schedule->call(function () {
    		Attendant::countCurrentDay();
    	})->dailyAt('23:30');
    	
    	// 每天的上午9点运行 推送前一天统计给部门领导
    	$schedule->call(function(){
    		Statics::pushDayOrWeekReportToLeaderOfDep(false);
    	})->dailyAt('9:00');
    	
    	// 每周一的上午9点运行 推送上一周考勤统计给部门领导
    	$schedule->call(function (){
		   Statics::pushDayOrWeekReportToLeaderOfDep(true);
		})->weekly()->mondays()->at('6:00');
    	
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
