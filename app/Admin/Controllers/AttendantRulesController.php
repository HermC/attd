<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\AttendantRule;
use App\Models\Department;
use App\Models\Employee;

class AttendantRulesController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('签到规则管理');
            $content->description('规则列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('签到规则管理');
            $content->description('编辑规则');

            $content->body($this->form()->edit($id));
        });
    }

    
    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('签到规则管理');
            $content->description('新增规则');

            $content->body($this->form());
        });
    }

    public function store(){
    
    	$form = $this->form();
    	$form->saving(function (Form $form) {
    		//远程创建一个部门
    		//$pos = $form->input("position");
    		$deps = $form->deps;
    		$days = $form->workday;
    		//$form->deps = array_filter($deps);
    		//$form->workday = array_filter($days);
    		//dd(array_filter($days));
    		//$form->deps = implode(",", $deps);
    		//$form->workday = implode(",", $days);
    		//dump(implode(",", $days));
    		//dd(implode(",", $deps));
    		//dd($days);
    	});
    	
    	return $form->store();
    
    }
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(AttendantRule::class, function (Grid $grid) {

        	/* $user = Admin::user();
        	if( ! $user->isRole('Administrator') ){
        		$grid->model()->whereIn( "depart_id",$user->departments() )->orderBy('id', 'desc');
        	}else{
        		$grid->model()->orderBy('id', 'desc');;
        	} */
            $grid->title('规则名称');
            $grid->deps('绑定部门')->display(function($dep) {
            	$deps = explode(",", $dep);
            	$str = '';
            	$departs =  Department::whereIn('id',$deps)->get();
            	foreach ($departs as $d){
            		$str .= "<span class='badge bg-green'>{$d->name}</span>";
            	}
            	return $str;
            });
            $grid->disableExport();
            $grid->sigin_time('签到时间');
            $grid->sigout_time('签退时间');
            $grid->created_at('创建时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(AttendantRule::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text("title","规则名称")->rules('bail|required');//|unique:attendance_rules
            //$form->select("sigin_time","签到")->options(["7:30" => "7:30", "8:00" => "8:00" , "8:30" => "8:30","9:00" => "9:00" ]);
            //$form->text("sigout_time","签退")->options(["4:30" => "4:30", "5:00" => "5:00" , "5:30" => "5:30","6:00" => "6:00","6:30" => "6:30" ]);
            $form->checkbox("deps","选择部门")->options(Department::where('id','<>',1)->get()->pluck('name', 'id') )->rules('required');
            $form->time("sigin_time","签到")->format('HH:mm');
            $form->time("sigout_time","签退")->format('HH:mm');
            $form->select("time_span","签到时间范围")->options(["1" => "1分钟", "5" => "5分钟" , "10" => "10分钟","15" => "15分钟","30" => "30分钟" ]);
            $form->checkbox("workday","工作日选择")->options([1=> "星期一", 2 => "星期二" , 3=> "星期三", 4 => "星期四", 5 => "星期五", 6=> "星期六", 7 => "星期日" ])->rules('required');
            $form->select("dist_span","签到距离范围")->options(["100" => "100米", "200" => "200米" , "500" => "500米","1000" => "1000米","2000" => "2000米" ]);
            $form->baiduMap('latitude','longitude' , "position","签到位置");
            $exs = Employee::select('id','name')->pluck('name', 'id')->all();
            $form->multipleSelect("auto_attend","自动考勤")->options($exs);//->ajax('/admin/api/employee');
            //$form->hidden("longitude")->attribute(['id' => 'longitude']);
            //$form->hidden("latitude")->attribute(['id' => 'latitude']);
            
        });
    }
}
