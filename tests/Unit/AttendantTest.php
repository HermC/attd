<?php

namespace Tests\Unit;

use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Employee;
use App\Models\AttendantRule;
use Carbon\Carbon;
use App\Models\Attendant;
use App\Models\Vocation;

class AttendantTest extends TestCase
{
  use DatabaseTransactions;
  
    /**
     * A basic test example.
     *
     * @return void
     */
    /* public function testAutoAttend()
    {
      
      //Department::departNotifyScheduel();
      //Attendant::autoCheckAttendant();
       //$result =  Department::notify(1, 3);
       //$result = Department::pushReportToDepartLeader(1, 3);
       //dd($result);
    } */
    
  private  $employee;
  //使用规则14
  private $rule ;
  private $gps ;
  private $location ;
  private $memo ;
  
  public function setUp(){
    parent::setUp();
    //使用杨昇这个账号
    $this->employee = Employee::find(3);
    //使用规则14 9:00   17:00
    $this->rule = AttendantRule::find(14);
    $this->gps = ['118.741745','32.01001'];
    $this->location = "南京市建邺区西堤国际一区南门站自行车租赁点";
    $this->memo ="这是一条外勤测试";
    
  }
	

  /**
   *自动签到
   *
   */
  /* public function  testAutoAttendant(){

    Attendant::specialAttendant();
    $attendants = Attendant::all();
    $this->assertEquals($outs, 0);
    $this->assertEquals($counts["early"], 0.5);
    $this->assertEquals($counts["abcent"], 0.5);
  
  } */



  /**
   *签到正常 签退9-12
   *
   */
   public function  testAttendant2(){

       $intime = Carbon::createFromFormat("H:i", "8:30");
       $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
       $this->assertEquals($res1[0], true);
       $outtime = Carbon::createFromFormat("H:i", "10:00");
       $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
       $this->assertEquals($res2[0], true);
       $attendant = $this->employee->attendantOfDate();
       list($outs,$counts) = $attendant->getAttendantResult();
       $this->assertEquals($outs, 0);
       $this->assertEquals($counts["early"], 0.5);
       $this->assertEquals($counts["abcent"], 0.5);

   }

   /**
    *签到正常 签退12点-13点
    *
    */
   public function  testAttendant3(){
      $intime = Carbon::createFromFormat("H:i", "8:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);
      $outtime = Carbon::createFromFormat("H:i", "12:30");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);

      $this->assertEquals($res2[0], true);
      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();
      $this->assertEquals($outs, 0);
      //dd($counts);
      $this->assertEquals($counts["normal"], 0.5);
      $this->assertEquals($counts["abcent"], 0.5);
   }

   /**
    *签到正常 签退13点-17点
    *
    */
   public function  testAttendant4(){
      $intime = Carbon::createFromFormat("H:i", "8:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);
      $outtime = Carbon::createFromFormat("H:i", "15:30");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals($res2[0], true);
      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();
      $this->assertEquals($outs, 0);
      $this->assertEquals($counts["normal"], 0.5);
      $this->assertEquals($counts["early"], 0.5);
   }
   /**
    *签到正常 签退17点后
    *
    */
   public function  testAttendant5(){
    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    //dump($res1[1]->created_at);
    $this->assertEquals($res1[0], true);
    $outtime = Carbon::createFromFormat("H:i", "17:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    //dump($res2[1]->created_at);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals($outs, 0);
    $this->assertEquals($counts["normal"], 1);
   }

   /**
    *上午考勤 签退
    *
    */
   public function  testAttendant6(){
    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $tt = Carbon::createFromFormat("H:i", "10:30");
    $res3 = $this->employee->attendant(null,$this->gps,"外勤测试地址","这是一条外勤测试",$tt);
    $this->assertEquals($res3[0], true);
    $outtime = Carbon::createFromFormat("H:i", "10:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
  $this->assertEquals(0.5,$counts["abcent"]);
   }

   /**
    *上午考勤 签退
    *
    */
   public function  testAttendant7(){
    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $tt = Carbon::createFromFormat("H:i", "10:30");
    $res3 = $this->employee->attendant(null,$this->gps,"外勤测试地址","这是一条外勤测试",$tt);
    $this->assertEquals($res3[0], true);
    $outtime = Carbon::createFromFormat("H:i", "12:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);
   }

   /**
    *上午考勤 签退
    *
    */
   public function  testAttendant8(){
    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $tt = Carbon::createFromFormat("H:i", "10:30");
    $res3 = $this->employee->attendant(null,$this->gps,"外勤测试地址","这是一条外勤测试",$tt);
    $this->assertEquals($res3[0], true);
    $outtime = Carbon::createFromFormat("H:i", "15:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["early"]);
   }

   /**
    *上午考勤 签退
    *
    */
   public function  testAttendant9(){
    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $tt = Carbon::createFromFormat("H:i", "10:30");
    $res3 = $this->employee->attendant(null,$this->gps,"外勤测试地址","这是一条外勤测试",$tt);
    $this->assertEquals($res3[0], true);
    $outtime = Carbon::createFromFormat("H:i", "18:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(1,$counts["normal"]);
   }

   /**
    *上午考勤 签退
    * 10 ~ 13
    */
   public function  testAttendant10(){
    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $outtime = Carbon::createFromFormat("H:i", "10:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $tt = Carbon::createFromFormat("H:i", "14:30");
    $res3 = $this->employee->attendant(null,$this->gps,"外勤测试地址","这是一条外勤测试",$tt);
    $this->assertEquals($res3[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(1,$counts["normal"]);
   }

   /**
    *上午考勤 签退
    * 14
    */
   public function  testAttendant14(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $outtime = Carbon::createFromFormat("H:i", "11:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["abcent"]);
   }

   /**
    *上午考勤 签退
    * 14
    */
   public function  testAttendant15(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $outtime = Carbon::createFromFormat("H:i", "12:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["abcent"]);
   }


   /**
    *上午考勤 签退
    * 14
    */
   public function  testAttendant16(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $outtime = Carbon::createFromFormat("H:i", "15:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["early"]);
   }


   /**
    *上午考勤 签退
    * 14
    */
   public function  testAttendant17(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $outtime = Carbon::createFromFormat("H:i", "17:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //  dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["normal"]);
   }

   /**
    *上午考勤 签退
    * 14
    */
   public function  testAttendant18(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $this->submitOutdoorRecords("11:35");
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    $outtime = Carbon::createFromFormat("H:i", "11:30");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["abcent"]);
    $this->assertEquals(0.5,$counts["normal"]);
   }


   public  function submitOutdoorRecords($time){
      $tt = Carbon::createFromFormat("H:i", $time);
      $res3 = $this->employee->attendant(null,$this->gps,"外勤测试地址","这是一条外勤测试",$tt);
      $this->assertEquals($res3[0], true);
   }

   /**
    *上午考勤 签退
    * 14
    */
   public function  testAttendant19(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $this->submitOutdoorRecords("10:50");
    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    $outtime = Carbon::createFromFormat("H:i", "12:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);

    //  dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["abcent"]);
    $this->assertEquals(0.5,$counts["normal"]);
   }

   public function  testAttendant20(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);
    $this->submitOutdoorRecords("10:50");
    $attendant = $this->employee->attendantOfDate();
    $outtime = Carbon::createFromFormat("H:i", "15:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);
    list($outs,$counts) = $attendant->getAttendantResult();

    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["early"]);
    $this->assertEquals(0.5,$counts["normal"]);
   }

   public function  testAttendant21(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("10:50");

    $outtime = Carbon::createFromFormat("H:i", "18:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();

    $this->assertEquals(1,$outs );
    $this->assertEquals(1,$counts["normal"]);
   }

   public function  testAttendant22(){
    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $outtime = Carbon::createFromFormat("H:i", "10:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);

    $this->submitOutdoorRecords("14:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();

    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["normal"]);
   }

    public function  testAttendant23(){
      $intime = Carbon::createFromFormat("H:i", "10:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);

      $outtime = Carbon::createFromFormat("H:i", "12:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals($res2[0], true);

      $this->submitOutdoorRecords("14:50");

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();

      $this->assertEquals(1,$outs );
      $this->assertEquals(0.5,$counts["late"]);
      $this->assertEquals(0.5,$counts["normal"]);
   }
    public function  testAttendant28(){
    $intime = Carbon::createFromFormat("H:i", "12:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $outtime = Carbon::createFromFormat("H:i", "16:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals($res2[0], true);

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();

    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["early"]);
    $this->assertEquals(0.5,$counts["abcent"]);
   }

   public function  testAttendant29(){
      $intime = Carbon::createFromFormat("H:i", "12:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);

      $outtime = Carbon::createFromFormat("H:i", "18:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals($res2[0], true);

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();

      $this->assertEquals(0,$outs );
      $this->assertEquals(0.5,$counts["normal"]);
      $this->assertEquals(0.5,$counts["abcent"]);
   }

   public function  testAttendant32(){

      $this->submitOutdoorRecords("10:50");

      $outtime = Carbon::createFromFormat("H:i", "15:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals($res2[0], true);

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();

      $this->assertEquals(1,$outs );
      $this->assertEquals(0.5,$counts["normal"]);
      $this->assertEquals(0.5,$counts["early"]);

   }
    public function  testAttendant33(){

      $this->submitOutdoorRecords("10:50");

      $outtime = Carbon::createFromFormat("H:i", "18:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals( true,$res2[0]);

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();

      $this->assertEquals(1,$outs );
      $this->assertEquals(1,$counts["normal"]);

   }
   public function  testAttendant35(){

      $intime = Carbon::createFromFormat("H:i", "12:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);

      $outtime = Carbon::createFromFormat("H:i", "12:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals( true,$res2[0]);

      $this->submitOutdoorRecords("15:50");

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();

      $this->assertEquals(1,$outs );
      $this->assertEquals(0.5,$counts["normal"]);
      $this->assertEquals(0.5,$counts["abcent"]);
   }

   public function  testAttendant40(){

      $intime = Carbon::createFromFormat("H:i", "14:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);

      $outtime = Carbon::createFromFormat("H:i", "15:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals( true,$res2[0]);

      //$this->submitOutdoorRecords("15:50");

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();

      $this->assertEquals(0,$outs );
      $this->assertEquals(0.5,$counts["early"]);
      $this->assertEquals(0.5,$counts["abcent"]);

   }

   public function  testAttendant41(){

      $intime = Carbon::createFromFormat("H:i", "14:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);

      $outtime = Carbon::createFromFormat("H:i", "18:50");
      $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
      $this->assertEquals( true,$res2[0]);

      //$this->submitOutdoorRecords("15:50");

      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();
      //dd($counts);
      $this->assertEquals(0,$outs );
      $this->assertEquals(0.5,$counts["late"]);
      $this->assertEquals(0.5,$counts["abcent"]);

   }
   public function  testAttendant48(){

    $intime = Carbon::createFromFormat("H:i", "14:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $outtime = Carbon::createFromFormat("H:i", "15:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals( true,$res2[0]);

    $this->submitOutdoorRecords("15:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);

  }
  public function  testAttendant49(){

    $intime = Carbon::createFromFormat("H:i", "14:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $outtime = Carbon::createFromFormat("H:i", "18:50");
    $res2 = $this->employee->attendant($this->rule,$this->gps,"","",$outtime);
    $this->assertEquals( true,$res2[0]);

    $this->submitOutdoorRecords("15:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);

  }
  public function  testAttendant2_2(){

    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);


    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);

  }
  public function  testAttendant2_3(){

    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("10:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);

  }
  public function  testAttendant2_4(){

    $intime = Carbon::createFromFormat("H:i", "8:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("14:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(1,$counts["normal"]);

  }
  public function  testAttendant2_5(){

    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("10:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["abcent"]);
    $this->assertEquals(0.5,$counts["normal"]);
  }
  public function  testAttendant2_6(){

    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("15:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["normal"]);
  }
  public function  testAttendant2_7(){

    $intime = Carbon::createFromFormat("H:i", "10:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(0.5,$counts["late"]);
    $this->assertEquals(0.5,$counts["abcent"]);
  }
  public function  testAttendant2_8(){

    $intime = Carbon::createFromFormat("H:i", "12:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("10:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);
  }
  public function  testAttendant2_9(){

    $intime = Carbon::createFromFormat("H:i", "12:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("15:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["normal"]);
    $this->assertEquals(0.5,$counts["abcent"]);
  }
  public function  testAttendant2_10(){

    $intime = Carbon::createFromFormat("H:i", "15:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    //$this->submitOutdoorRecords("15:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(0,$outs );
    $this->assertEquals(1,$counts["abcent"]);
  }
  public function  testAttendant2_12(){

    $intime = Carbon::createFromFormat("H:i", "15:30");
    $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
    $this->assertEquals($res1[0], true);

    $this->submitOutdoorRecords("16:50");

    $attendant = $this->employee->attendantOfDate();
    list($outs,$counts) = $attendant->getAttendantResult();
    //dd($counts);
    $this->assertEquals(1,$outs );
    $this->assertEquals(0.5,$counts["abcent"]);
    $this->assertEquals(0.5,$counts["normal"]);
  }
  public function  testAttendant2_26(){

      $intime = Carbon::createFromFormat("H:i", "12:30");
      $res1 = $this->employee->attendant($this->rule,$this->gps,"","",$intime);
      $this->assertEquals($res1[0], true);


      $attendant = $this->employee->attendantOfDate();
      list($outs,$counts) = $attendant->getAttendantResult();
      //dd($counts);
      $this->assertEquals(0,$outs );
      $this->assertEquals(0.5,$counts["early"] );
      $this->assertEquals(0.5,$counts["abcent"]);
    }
}
