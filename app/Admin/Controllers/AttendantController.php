<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Employee;
use App\Models\Attendant;
use Encore\Admin\Widgets\Table;
use App\Models\Department;
class AttendantController extends Controller
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

            $content->header('考勤管理');
            $content->description('考勤记录列表');
            $content->body($this->grid());
            Admin::script($this->script());
            
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

            $content->header('考勤管理');
            $content->description('考勤记录详情');
			
            $content->body(view("admin.modify_attendant"));
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
            $content->header('考勤管理');
            $content->description('新增考勤规则');
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
        return Admin::grid(Attendant::class, function (Grid $grid) {

        	$user = Admin::user();
        	if( ! $user->isRole('Administrator') ){
        		$grid->model()->whereIn( "depart_id",$user->departments() )->orderBy('id', 'desc');
        	}else{
        		$grid->model()->orderBy('id', 'desc');;
        	}

        	
            $grid->id('ID')->sortable();
            //$grid->column("employee.name","员工姓名");
            //$grid->column("rule.title","考勤项目");
            $grid->column("department.name","部门");
            $grid->column("employee_name","员工姓名");
            /* $grid->column("week","考勤日")->display(function($val){
            	if($val!=7){
            		return "星期".$val;
            	}else{
            		return "星期日";
            	}
            }); */
         
            $grid->state('考勤状态')->display(function($state) {
            	switch ($state){
            		case 1:
            		    return "全勤";
            		 case 2:
            		   return "缺勤";
            		case 3:
            		  	return "休息";
            		default:
            		  return "异常状态";
            	}
            }); 
            	$grid->created_at("考勤时间");
            	$grid->column('expand', '考勤详情')->expand(function () {
            		$attend = Attendant::find($this->id);
            		$logs = $attend->logs()->select("state","memo","location","created_at","latitude","longitude","type","rule_id","id")->with("rule")->get();
            		$lc = "";
            		 
            		foreach( $logs as $log){
            			//dd($log);
            			$state = $log->getStateDesc();
            			if($log->type == 3){
            				$lc .= "<li>{$log->created_at}&nbsp;&nbsp; <span>类型：外勤</span>&nbsp;&nbsp;<span >结果：{$state}</span>&nbsp;&nbsp; 地点：{$log->location}&nbsp;&nbsp; 备注： {$log->memo}     </li>";
            			}else{
            				$rule = $log->rule;
            				$tc = ($log->type==1)?"签到":"签退";
            				if($rule){
            					$lc .= "<li>{$log->created_at}&nbsp;&nbsp; <span>类型：{$tc}</span>&nbsp;&nbsp; <span >结果：{$state}&nbsp;&nbsp; 考勤规则名称：{$rule->title}</span> <a href='javascript:;'  onclick='window.audit(this)'  class='btn  btn-default btn-xs  op'  data-op='modifyNomal' data-id='{$log->id}' >修改为正常</a>&nbsp;&nbsp;<input type='text'  value='' class='form-control' style='width:150px;display:inline-block'  id='memo_{$log->id}'  /> </li>";
            				}else{
            					//$state2 = $attend->state?"休息":"人未到现场";
            					$lc .= "<li>{$log->created_at}&nbsp;&nbsp; <span>类型：{$tc}</span>&nbsp;&nbsp; <span >结果：{$state }&nbsp;&nbsp; </span><a href='javascript:;'  onclick='window.audit(this)''  class='btn  btn-default btn-xs  op'  data-op='modifyNomal' data-id='{$log->id}' >修改为正常</a>&nbsp;&nbsp;<input type='text'  value='' class='form-control' style='width:150px;display:inline-block'  id='memo_{$log->id}'  /></li>";
            				}
            				
            			}
            		}
            		//$logs = $this->logs;
            		$content =  <<<EOT
            		<style>
            				.atDetail{
            				    list-style: none;
   								 padding: 10px 10px;
            				}
            				.atDetail li{
            					margin-bottom:10px
            				}
            		</style>
            		<ul class="atDetail">
            			{$lc}
            		</ul>
EOT;
            	            			//$profile = array_only($logs, ['homepage', 'gender', 'birthday', 'address', 'last_login_at', 'last_login_ip', 'lat', 'lng']);
            	
            	            			return $content;
            	            			 
            	}, '点击查看');
            $grid->actions(function($actions){
            	$row = $actions->row;
            	//dd($row);
            	//$actions->disableDelete();
            	$actions->disableEdit();
            });
            
            	$grid->filter(function($filter)use($user){
            		$filter->useModal();
            		$filter->like('employee_name', '员工姓名');
                    if( $user->isRole('Administrator') ){
                        $filter->is('depart_id', '部门')->select(Department::where("id","<>","1")->get()->pluck('name', 'id'));
                    }
            		$filter->between('created_at', '考勤时间')->datetime();
            	});
            //$grid->updated_at();
            $grid->disableCreation();
            $grid->disableExport();
        });
    }
    protected function script()
    {
    	return <<<'EOT'
    		 window.audit = function(target){
    			var op = $(target).attr('data-op');
			    var id = $(target).attr('data-id');
	    		var res = 0;
    			var rr = $("#memo_"+id).val();
    			console.log(rr);
	    		res = confirm("是否要进行操作？");
	    			if(res){
    					$.post('/admin/op',{id:id,op:op,rr:rr},function(data){
    					   //这里显示一下
	    					if(data.status){
    							 $.pjax.reload({container: "#pjax-container", timeout: 5000});
    						}
    					});
	    			}
	    		//更改状态
			};
EOT;
    }  
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Attendant::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
