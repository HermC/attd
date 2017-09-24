<?php

namespace App\Admin\Controllers;

use App\Models\Employee;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Department;
use Encore\Admin\Tree;
use Stoneworld\Wechat\Exception;
use Stoneworld\Wechat\Group;
use Illuminate\Support\MessageBag;
use App\Admin\Extensions\Tools\Synchronize;

class DepartmentController extends Controller
{
    use ModelForm ;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('部门管理');
            $content->description('部门列表');
            //Department::tree()
            $content->body($this->grid());
            
        });
    }

    public function destroy($id)
    {
        try{
            $this->form()->destroy($id);
            return response()->json([
                'status'  => true,
                'message' => trans('admin::lang.delete_succeeded'),
            ]);
        }catch (Exception $ex){

            return response()->json([
                'status'  => false,
                'message' => $ex->getMessage(),
            ]);
        }

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

            $content->header('部门管理');
            $content->description('编辑部门');

            $content->body($this->form($id)->edit($id));
        });
    }
    public function update($id){
    
    	$form = $this->form($id);
    	$form->saving(function (Form $form)use( $id ) {
    		$appId  = config("ent.appId");
    		$secret = config("ent.secret");
    		$group = new Group($appId, $secret);
    		$res = $group->update( $id , $form->input("name"),$form->input("parentid"),$form->input("order") );
    		if( $res["errmsg"] && $res["errmsg"]!=="updated" ){
    			$error = new MessageBag([
    					'title'   => '错误信息',
    					'message' => '远程更新失败',
    			]);
    			return back()->withInput()->with(compact('error'));
    		}
    	});
    	return $form->update( $id);
    	 
    }
    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('部门管理');
            $content->description('新增部门');

            $content->body($this->form());
        });
    }

    public function store(){
    
    	$form = $this->form();
    	$form->saving(function (Form $form) {
    		//远程创建一个部门
    		$appId  = config("ent.appId");
    		$secret = config("ent.secret");
    		$group = new Group($appId, $secret);
    		$gid = $group->create($form->input("name"  ), $form->input("parentid"));
    		if($gid<=0){
    			$error = new MessageBag([
    					'title'   => '错误信息',
    					'message' => '远程更新失败',
    			  ]);
    			return back()->withInput()->with(compact('error'));
    		}
    		$form->input( "id", $gid  );
    	});
    	/* $form->saved(function (Form $form) {
    		//TODO  跳转到show页面
    		$model = $form->model();
    		return redirect('/admin/off/'.$model->uuid);
    	}); */
    	return $form->store();
    
    }
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Department::class, function (Grid $grid) {
			$user = Admin::user();
        	if( ! $user->isRole('Administrator') ){
        		$grid->model()->where("id","<>",1)->whereIn("id",$user->departments());
        	}else{
        		$grid->model()->where("id","<>",1);
        	}
            $grid->id('ID')->sortable();
			$grid->name("部门名称");
			//$grid->order("排序")->editable();
            $grid->office_time("上班时间");
            $grid->quit_time("下班时间");
            $states = [
	            'on'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
	            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $grid->notify("是否推送")->switch($states);
            $grid->tools(function ($tools) {
            	$tools->append(new Synchronize(1));
            });
            	$grid->filter(function($filter){
            		//$filter->useModal();
            		$filter->like('name', '部门名称');
            		$filter->between('office_time', '上班时间')->datetime();
            		$filter->between('quit_time', '下班时间')->datetime();
            	});
          $grid->disableExport();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id=0)
    { 
        return Admin::form(Department::class, function (Form $form)use($id) {

            $form->display('id', 'ID');
            //$form->select('parentid',"上级部门")->options(Department::selectOptions())->rules('required');
            $form->hidden('parentid')->value(1);
            $form->text("name","部门名称")->rules('required');
            $form->time("office_time","上班提醒时间")->format('HH:mm')->rules('required');
            $form->time("quit_time","下班提醒时间")->format('HH:mm')->rules('required');
            $states = [
            'on'  => ['value' => 1, 'text' => '推送', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '不推送', 'color' => 'danger'],
            ];
            $form->switch("notify","是否推送")->states($states)->help("推送开关打开后将推送上下班提醒");
            if($id==0){
                $exs = Employee::select('id','name')->pluck('name', 'id')->all();
            }else{
                $exs = Employee::select('id','name')->whereRaw("find_in_set('$id',department)")->pluck('name', 'id')->all();
            }
            $form->select("charge","部门审批人")->options($exs)->help("所有涉及部门审核的申请，将由部门审批人进行审核");
            $form->multipleSelect("leaders","部门领导")->options($exs)->help("每天、每周 考勤报告推送给此处设置的领导");
            //$form->number('order', '排序');
            //$form->display('created_at', 'Created At');
            //$form->display('updated_at', 'Updated At');
            
        });
    }
}
