<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Employee;
use App\Models\Department;
use App\Admin\Extensions\Tools\Synchronize;
use Stoneworld\Wechat\User;
use Illuminate\Support\MessageBag;

class EmployeeController extends Controller
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

            $content->header('员工管理');
            $content->description('企业员工列表');

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

            $content->header('员工管理');
            $content->description('编辑企业员工');

            $content->body($this->form($id)->edit($id));
        });
    }

    
    public function store(){
    
    	$form = $this->form();
    	$form->saving(function (Form $form) {
    		$deps = array_filter($form->input("department"));
    		/*if(!in_array(1, $deps)){
    			array_unshift($deps,"1");
    		}*/
    		//远程创建一个用户
    		$appId  = config("ent.appId");
    		$secret = config("ent.secret");
    		$user = new User($appId, $secret);
    		$wxdata = [
	    		"userid"=> $form->input("userid"),
	    		"name"=>  $form->input("name"),
	    		"department"=>  $form->input("department"), //implode(',' , $this->department)
	    		"position"=>  $form->input("position"),
	    		"mobile"=>  $form->input("mobile"),
	    		"gender"=>  $form->input("gender"),
	    		"email"=>  $form->input("email"),
	    		"weixinid"=>  $form->input("weixinid"),
    		//"avatar_mediaid"=> "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
    		];
    		$res = $user->create($wxdata);
    		if( $res["errmsg"] && ($res["errmsg"]!=="created"  )){
    			$error = new MessageBag([
    					'title'   => '错误信息',
    					'message' => '远程更新失败',
    					]);
    			return back()->withInput()->with(compact('error'));
    		}
    		
    		$form->input("department",$deps);
    		$form->input("dep",$deps[count($deps)-1]);
    		
    	});
    	
    	return $form->store();
    
    } 
    
    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('员工管理');
            $content->description('新增企业员工');
			
            $content->body($this->form());
        });
    }
    public function update($id){
    	
    	$form = $this->form();
    	$form->saving(function (Form $form)use( $id ) {
    		$deps = array_filter($form->input("department"));
    		
    		/*if(!in_array(1, $deps)){
    			array_unshift($deps,"1");
    		}*/
    		
    	//远程创建一个用户
    		$appId  = config("ent.appId");
    		$secret = config("ent.secret");
    		$user = new User($appId, $secret);
    		$wxdata = [
	    		"userid"=> $form->input("userid"),
	    		"name"=>  $form->input("name"),
	    		"department"=>  $deps, //implode(',' , $this->department) //$form->input("department")
	    		"position"=>  $form->input("position"),
	    		"mobile"=>  $form->input("mobile"),
	    		"gender"=>  $form->input("gender"),
	    		"email"=>  $form->input("email"),
	    		"weixinid"=>  $form->input("weixinid"),
    			//"avatar_mediaid"=> "2-G6nrLmr5EC3MNb_-zL1dDdzkd0p7cNliYu9V5w7o8K0",
    		];
    		$res = $user->update($wxdata);
    		
    		if( $res["errmsg"] && ($res["errmsg"]!=="updated") ){
    			$error = new MessageBag([
    					'title'   => '错误信息',
    					'message' => '远程更新失败',
    			]);
    			return back()->withInput()->with(compact('error'));
    		}
    		
    		$form->input("department",$deps);
    		$form->input("dep",$deps[count($deps)-1]);
    	});
    	return $form->update( $id);
    
    } 
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Employee::class, function (Grid $grid) {

        	$user = Admin::user();
        	if( ! $user->isRole('Administrator') ){
        		$grid->model()->whereIn("dep",$user->departments());
        	}else{
        		$grid->model();
        	} 
        	
            $grid->id('ID')->sortable();
            $grid->userid('微信端用户ID')->sortable();
            $grid->weixinid('微信号');
            $grid->name('成员名称')->sortable();
            $grid->column('department', '部门')->display(function($dep) {
            	$deps = explode(",", $dep);
            	$str = '';
            	$departs =  Department::whereIn('id',$deps)->get();
            	foreach ($departs as $d){
            			$str .= "<span class='badge bg-green'>{$d->name}</span>";
            	}
            	return $str;
            });
            $grid->position('职位');
            $grid->mobile('手机号码');
            $grid->email('邮箱');
            $grid->gender('性别')->display(function($state) {
            	switch ($state){
            		case 1:
            			return "男性";
            		case 2:
            			return "女性";
            		default:
            			return "未知";
            	}
            });
           	$grid->filter(function($filter){
            		$filter->useModal();
            		$filter->like('weixinid', '微信号');
            		$filter->like('name', '成员名称');
            		$filter->like('userid', '用户ID');
            		$filter->like('position', '职位');
            		$filter->like('mobile', '手机号');
            		$filter->like('email', '邮箱');
            		/* $filter->where(function ($query) {
            			
            			$query->whereRaw('department', 'like', "%{$this->input}%")
            			->orWhere('content', 'like', "%{$this->input}%");
            		
            		}, '部门');  */
            		//$filter->is('employee_id', '部门')->select(Department::where("id","<>","1")->get()->pluck('name', 'id'));
            		//$filter->between('created_at', '注册时间')->datetime();
            	});
            	$grid->tools(function ($tools) {
            		$tools->append(new Synchronize(2));
            	});
            $grid->created_at("创建时间");
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

        return Admin::form(Employee::class, function (Form $form)use($id) {
            if($id!=0){
                $user = Employee::find($id);
                $admin = Admin::user();
                if( ! $admin->isRole('Administrator') ){
                    $dep = $user->getDepartment();
                    $adminDeps = explode(",",$admin->department);
                    $res = in_array($dep->id,$adminDeps);
                    if(!$res){
                        throw new \Exception('非法访问');
                    }
                }
            }

            $form->display('id', 'ID');
           /*  if($id){
            	$rules = "required";
            }else{
            	$rules = "required|unique:employee,userid";
            } */
            $form->text("userid","微信用户标识")->rules('required')->help('微信端用户id，确保唯一，示例：zhangsan');
            $form->text("weixinid","微信号")->help('微信号，确保唯一，示例：打开"我" ，昵称下的微信号');
            $form->text("name","员工姓名")->rules('required');
            $form->multipleSelect("department","部门")->options(Department::all()->pluck('name', 'id'))->rules('required');
            //$form->text("position","职位")->rules('required');
            $form->select("position","职位")->options(["普通员工"=>"普通员工","部门副职"=>"部门副职","部门正职"=>"部门正职","人力资源部长"=>"人力资源部长","分院长"=>"分院长","院长"=>"院长"])->rules('required');
            $form->text("mobile","手机")->rules('required');;
            $form->text("email","邮箱");
            $form->radio("gender","性别")->options([1 => '男', 2=> '女'])->default('m')->rules('required');;
            if($id){
            	$states = [
	            	'on'  => ['value' => 1, 'text' => '有效', 'color' => 'success'],
	            	'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
            	];
            	$form->switch("enable", "状态")->states($states);
            }
            $form->hidden("dep");
            //$form->display('created_at', 'Created At');
            //$form->display('updated_at', 'Updated At');
        });
    }
}
