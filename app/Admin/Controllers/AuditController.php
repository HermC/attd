<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Employee;
use App\Models\VocationRule;
use App\Models\AuditRule;

class AuditController extends Controller
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

            $content->header('审核配置管理');
            $content->description('审核配置列表');
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

            $content->header('审核配置管理');
            $content->description('编辑审核配置');

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

            $content->header('审核配置管理');
            $content->description('新增审核配置');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(AuditRule::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->title('审核规则');
            //$grid->people('审核人')->checkbox(["1" => "部长", "2" => "人力资源部长" , "3" => "分管院长","4" => "院长" ]);
             $grid->people('审核人')->display(function($val){
            	$p = explode(",", $val);
            	return Employee::whereIn("id",$p)->get()->pluck("name")->implode('=>');
            	
            });
            //$grid->audit_rule_id("审核制度")->select($exs);
            $grid->disableExport();
            
            /* $grid->created_at();
            $grid->updated_at(); */
            
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(AuditRule::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text("title","规则流程")->help("描述审核流转，=>为特殊分隔符，按照此格式修改，注意请确保此职位有对应的员工，否则将出错");
            $exs = Employee::select('id','name')->pluck('name', 'id')->all();
            /*$hr =  Employee::where("position","like","%人力资源部长%")->first();
            if(isset($hr)){
                $exs->prepend($hr->name,$hr->id);
                $auditors[]=$hr->id;
            }
            $vice =  Employee::where("position","like","%分管院长%")->first();
            if(isset($vice)){
                $exs->prepend($vice->name,$vice->id);
                $auditors[]=$vice->id;
            }
            $cheif =  Employee::where("position","like","%院长%")->first();
            if( isset($cheif) ){
                $exs->prepend($cheif->name,$cheif->id);
                $auditors[]=$cheif->id;
            }*/
            $form->multipleSelect("people","核准人")->options( $exs )->help("此处可以直接制定此规则对应的审核人，按照次序输入，优先级大于规则流程，留空则按照流转规则进行");//->ajax('/admin/api/employee');
        });
    }
}
