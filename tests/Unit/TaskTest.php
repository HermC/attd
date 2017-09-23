<?php

namespace Tests\Unit;

use App\Models\Attendant;
use App\Models\Statics;
use Services\ServerChanSend;
use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Employee;
use App\Models\AttendantRule;
use Carbon\Carbon;
use App\Models\Vocation;
use App\Models\VocationDays;
use App\Models\Department;

class TaskTest extends TestCase
{
	use DatabaseTransactions;
	
    /**
     * A basic test example.
     *
     * @return void
     */
    
	private  $employee;
	
	public function setUp(){
		parent::setUp();
		//使用杨昇这个账号
		$this->employee = Employee::find(2);
	}
	
	//测试下班通知
	public function testNotifyDepOff(){
		Department::departNotifyScheduel(2);
	}

    //测试上班通知
    public function testNotifyDepOn(){
        Department::departNotifyScheduel(1);
    }

    //补全签到测试
    public function testAttendantComplete(){
        Attendant::autoCheckAttendant();
    }
    //自动考勤
    public  function testAutoAttendant(){
        Attendant::specialAttendant();
    }
    //全天统计
    public  function testCountCurrentDay(){
        Attendant::countCurrentDay();
    }

    //9点后推送日报告
    public  function testPushReportAt9PM(){
        Statics::pushDayOrWeekReportToLeaderOfDep(false);
    }

    //星期一推送报告
    public  function testPushReportAtMonday(){
        Statics::pushDayOrWeekReportToLeaderOfDep(true);
    }
}
