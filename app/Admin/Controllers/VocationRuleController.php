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
use Encore\Admin\Form\NestedForm;

class VocationRuleController extends Controller
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

            $content->header('请假规则管理');
            $content->description('请假规则列表');
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

            $content->header('请假规则管理');
            $content->description('编辑请假规则');
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

            $content->header('请假规则管理');
            $content->description('新增请假规则');
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
    	//$exs = AuditRule::select('id','title')->get()->pluck('title', 'id')->all();
        return Admin::grid(VocationRule::class, function (Grid $grid) {

        	/* $user = Admin::user();
        	if( ! $user->isRole('Administrator') ){
        		$grid->model()->whereIn("dep",$user->departments())->orderBy('id', 'desc');;
        	}else{
        		$grid->model()->orderBy('id', 'desc');;
        	} */
            $grid->id('ID')->sortable();
            $grid->title('规则名称');
           /*  $grid->audit_rule_id("审核制度")->select($exs); */
           // $grid->days('可请假天数');
            $states = [
	            'on'  => ['value' => 1, 'text' => '不需要', 'color' => 'success'],
	            'off' => ['value' => 2, 'text' => '需要', 'color' => 'danger'],
            ];
            $grid->proof('是否需要附件证明')->switch($states);
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
        return Admin::form(VocationRule::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text("title","请假标题");
            //$form->number("days","可以请假的天数");
            //$form->select("请假天数")

          /*   $exs = AuditRule::select('id','title')->get()->pluck('title', 'id')->all();
            $form->select("audit_rule_id","审核规则")->options($exs); */
            $states = [
	            'on'  => ['value' => 1, 'text' => '不需要', 'color' => 'success'],
	            'off' => ['value' => 2, 'text' => '需要', 'color' => 'danger'],
            ];
           
            $form->switch("proof","是否需要附件证明")->states($states);
            
            $exs = AuditRule::select('id','title')->get()->pluck('title', 'id')->all();
            
            $form->hasMany('rules', '绑定审核规则(可多个)' , function (Form\NestedForm $form)use($exs) {
            	$form->multipleSelect("target","审核对象")->options([
					1=>"普通员工",
            		2=>"部门副职",
            		3=>"部门正职",
            	]);
            	$form->number('mindays',"请假天数下限")->help("如果不规定具体天数，留0");
            	$form->number('maxdays',"请假天数上限")->help("如果不规定具体天数，留0");
           		$form->select("audit_id","审核规则")->options($exs);
            });
            
            
            
        });
    }
}
