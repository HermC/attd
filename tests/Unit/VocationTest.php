<?php

namespace Tests\Unit;

use App\Models\Department;
use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Employee;
use App\Models\AttendantRule;
use Carbon\Carbon;
use App\Models\Vocation;
use App\Models\VocationDays;

class VocationTest extends TestCase
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
	/**
	 * 审核请假流程
	 *
	 */
	public function testAuditVocation(){
		//新建一个假期
        // 断言：假期是否存在 审核记录是否存在 审核人是否一致 假期状态是否为审核中 审核单为等待审核
        // 模拟审核人 审核假期 ，
        // 断言：检查是否产生了新的审核记录，审核人为审核规则里的下一位，如果是最后一位，审核后看假期是否变为审核完成。
        $data = [];
        $data["employee_name"] = $this->employee->name;
        $data["employee_id"] = $this->employee->id;
        $data["depart_name"] =$this->employee->getDepartment()->name;
        $data["depart_id"] = $this->employee->departmentId();
        $data["audit_id"] = 0; //先设置为0 这里要根据天数判断审核规则
        $data["vocation_rule_id"] = 18;
        $data["start_time"] = Carbon::now()->addDays(1)->format("Y-m-d");
        $sType = 2;
        $data["end_time"] =  Carbon::now()->addDays(3)->format("Y-m-d");
        $eType = 1;
        $data["memo"] = "这是一条假期测试";
        $data["type"] = 1; //废用
        list($flag,$vocation) = Vocation::applyVocation($data, $sType, $eType,$this->employee);
        $this->assertEquals(true,$flag);//成功生成假期数据
        $this->assertInstanceOf(Vocation::class, $vocation);//
        $this->assertCount( 4, $vocation->vdays );//应该有请假四条记录
        $this->assertEquals(2, $vocation->days);//检查总天数

        //查看一下规则
        $vocation = Vocation::find($vocation->id);
        $rule = $vocation->auditRule;
        $this->assertNotNull($rule);
        $auditors = explode(",",$rule->people);


        $audits = $vocation->audit;
        $this->assertCount(1,$audits);//此时审核信息自动生成
        $this->assertEquals($audits[0]->audit_user,$auditors[0]);//第一条记录的审核人和规则的第一个审核人相同

        //假设审核通过
        list($result,$flag) = $audits[0]->makeAudit(1,"");
        $this->assertEquals(true,$result);
        $this->assertEquals("next",$flag);
        $vocation = Vocation::find($vocation->id);
        $audits = $vocation->audit;
        $this->assertCount(2,$audits);
        $this->assertEquals($audits[1]->audit_user,$auditors[1]);//第一条记录的审核人和规则的第一个审核人相同

        //假设审核通过
        list($result,$flag) = $audits[1]->makeAudit(1,"");
        $this->assertEquals(true,$result);
        $this->assertEquals("next",$flag);
        $vocation = Vocation::find($vocation->id);
        $audits = $vocation->audit;
        $this->assertCount(3,$audits);
        $this->assertEquals($audits[2]->audit_user,$auditors[2]);//第一条记录的审核人和规则的第一个审核人相同

        //假设审核通过
        list($result,$flag) = $audits[2]->makeAudit(1,"");
        $this->assertEquals(true,$result);
        $this->assertEquals("complete",$flag);
        $vocation = Vocation::find($vocation->id);
        $audits = $vocation->audit;
        $this->assertCount(3,$audits);
        $this->assertEquals($audits[2]->audit_user,$auditors[2]);//第一条记录的审核人和规则的第一个审核人相同

        $vocation = Vocation::find($vocation->id);
        $this->assertEquals( Vocation::R_APPROVED,$vocation->result );
        $this->assertEquals( Vocation::STATUS_COMPLETE,$vocation->state );
	}

    /**
     * 审核请假流程
     *
     */
    public function testRejectVocation(){
        //新建一个假期
        // 断言：假期是否存在 审核记录是否存在 审核人是否一致 假期状态是否为审核中 审核单为等待审核
        // 模拟审核人 审核假期 ，
        // 断言：检查是否产生了新的审核记录，审核人为审核规则里的下一位，如果是最后一位，审核后看假期是否变为审核完成。

        $data = [];
        $data["employee_name"] = $this->employee->name;
        $data["employee_id"] = $this->employee->id;
        $data["depart_name"] =$this->employee->getDepartment()->name;
        $data["depart_id"] = $this->employee->departmentId();
        $data["audit_id"] = 0; //先设置为0 这里要根据天数判断审核规则
        $data["vocation_rule_id"] = 18;
        $data["start_time"] = "2017-6-25";
        $sType = 2;
        $data["end_time"] =  "2017-6-27";
        $eType = 1;
        $data["memo"] = "这是一条假期测试";
        $data["type"] = 1; //废用
        list($flag,$vocation) = Vocation::applyVocation($data, $sType, $eType,$this->employee);
        $this->assertEquals(true,$flag);//成功生成假期数据
        $this->assertInstanceOf(Vocation::class, $vocation);//
        $this->assertCount( 4, $vocation->vdays );//应该有请假四条记录
        $this->assertEquals(2, $vocation->days);//检查总天数

        //查看一下规则
        $vocation = Vocation::find($vocation->id);
        $rule = $vocation->auditRule;
        $this->assertNotNull($rule);
        $auditors = explode(",",$rule->people);


        $audits = $vocation->audit;
        $this->assertCount(1,$audits);//此时审核信息自动生成
        $this->assertEquals($audits[0]->audit_user,$auditors[0]);//第一条记录的审核人和规则的第一个审核人相同

        //假设审核拒绝
        list($result,$flag) = $audits[0]->makeAudit(2,"测试看看");
        $this->assertEquals(false,$result);
        $this->assertEquals("rejected",$flag);
        $vocation = Vocation::find($vocation->id);
        $audits = $vocation->audit;
        $this->assertCount(2,$audits);
        $this->assertEquals($audits[1]->audit_user,$auditors[1]);//第一条记录的审核人和规则的第一个审核人相同
        $this->assertEquals( Vocation::R_REJECTED,$vocation->result );
        $this->assertEquals( Vocation::STATUS_COMPLETE,$vocation->state );

    }
	/**
	 * 申请休假 用例：杨昇 2017-6-12 下午  2017-6-14 上午
	 * 
	 */
	 public function  testApplyVocation(){
  
   		$data = [];
		$data["employee_name"] = $this->employee->name;
		$data["employee_id"] = $this->employee->id;
		$data["depart_name"] =$this->employee->getDepartment()->name;
		$data["depart_id"] = $this->employee->departmentId();
		$data["audit_id"] = 0; //先设置为0 这里要根据天数判断审核规则
		$data["vocation_rule_id"] = 18;
		$data["start_time"] = "2017-6-17";
		$sType = 2;
		$data["end_time"] =  "2017-6-19";
		$eType = 1;
		$data["memo"] = "这是一条假期测试";
		$data["type"] = 1; //费用
	    list($flag,$msg) = Vocation::applyVocation($data, $sType, $eType,$this->employee);
	    $this->assertEquals(true,$flag);
	    $this->assertInstanceOf(Vocation::class, $msg);
	    $this->assertCount( 4, $msg->vdays );
	    $this->assertEquals(2, $msg->days);
  } 
  
  /**
   * 申请休假 用例：杨昇 2017-6-12 下午  半天假
   *
   */
  public function  testApplyHaleVocation(){
  
	  	$data = [];
	  	$data["employee_name"] = $this->employee->name;
	  	$data["employee_id"] = $this->employee->id;
	  	$data["depart_name"] =$this->employee->getDepartment()->name;
	  	$data["depart_id"] = $this->employee->departmentId();
	  	$data["audit_id"] = 0; //先设置为0 这里要根据天数判断审核规则
	  	$data["vocation_rule_id"] = 18;
	  	$data["start_time"] = "2017-6-12";
	  	$sType = 2;
	  	$data["end_time"] =  "2017-6-12";
	  	$eType = 2;
	  	$data["memo"] = "这是一条假期测试";
	  	$data["type"] = 1; //费用
	  	list($flag,$msg) = Vocation::applyVocation($data, $sType, $eType,$this->employee);
	  	$this->assertEquals(true,$flag);
	  	$this->assertInstanceOf(Vocation::class, $msg);
	  	$this->assertCount( 1 , $msg->vdays );
	  	$this->assertEquals(0.5 , $msg->days);
  }
  
  /**
   * 申请休假 用例：请假后也算是假日
   *
   */
  public function   testVocationIsHoliday(){
  
  	$data = [];
  	$data["employee_name"] = $this->employee->name;
  	$data["employee_id"] = $this->employee->id;
  	$data["depart_name"] =$this->employee->getDepartment()->name;
  	$data["depart_id"] = $this->employee->departmentId();
  	$data["audit_id"] = 0; //先设置为0 这里要根据天数判断审核规则
  	$data["vocation_rule_id"] = 18;
  	$data["start_time"] = "2017-7-12";
  	$sType = 1;
  	$data["end_time"] =  "2017-7-12";
  	$eType = 1;
  	$data["memo"] = "这是一条假期测试";
  	$data["type"] = 1; //费用
  	list($flag,$msg) = Vocation::applyVocation($data, $sType, $eType,$this->employee);
  	$this->assertEquals(false,$flag);
  	//dump($msg->vdays);
  	$vocation1  = Vocation::todayIsHoliday($this->employee->id,null,"2017-7-12 10:50:00");
  	$this->assertInstanceOf(VocationDays::class, $vocation1);//因为现在需要审核
  	$vocation2  = Vocation::todayIsHoliday($this->employee->id,null,"2017-6-12 15:50:00");
  	$this->assertEquals(false, $vocation2);
  	
  }
  
  /**
   * 申请休假 用例：今天是否是休假，要判断上下午
   *
   */
  public function  testWeekendIsHoliday(){
  
  	$weekend  = Vocation::todayIsHoliday($this->employee,null,"2017-6-11 8:50:00");
  	$this->assertEquals(true, $weekend);
  	 
  }
  
}
