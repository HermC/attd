<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Employee;
use App\Models\Vocation;
use App\Admin\Extensions\VocationExport;
use Carbon\Carbon;
use App\Models\Department;

class VocationController extends Controller
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

            $content->header('请假管理');
            $content->description('请假列表');

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

            $content->header('请假管理');
            $content->description('编辑假期');

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

            $content->header('请假管理');
            $content->description('新增企业员工');

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
    	
        return Admin::grid(Vocation::class, function (Grid $grid) {
        	$user = Admin::user();
        	if( ! $user->isRole('Administrator') ){
        		$grid->model()->whereIn( "depart_id",$user->departments() )->orderBy('id', 'desc');
        	}else{
        		$grid->model()->orderBy('id', 'desc');;
        	}
			
            $grid->id('ID')->sortable();
            $grid->employee_name('请假人');
            $grid->depart_name('所在部门');
			$grid->column("rule.title","请假类型");
			$grid->start_time("开始时间")->display(function($val){
				return Carbon::createFromFormat("Y-m-d H:i:s", $val)->toDateString();
			});
			$grid->days("请假天数")->display(function($val){
			  if($val<1){
			  	return $val."天";
			  }	else{
			  	 return intval($val)."天";
			  }
			});
				$grid->result("状态")->display(function($val){
					if($val==1){
						return "批准";
					}	else{
						return "否决";
					}
				});
			$grid->column('detail', '详情')->expand(function() {
				$attach = !empty($this->attach)?'<p><a href="/upload'.$this->attach.'" target="_blank"><img src="/upload'.$this->attach.'" alt=""  style="width:200px;;" /></a></p>':"";
				$content =  <<<EOT
					<div class="container" style="width:100%">
						<div class="row">
	            			<div class="col-md-4">
								<h4 >事由备注:$this->memo</h4>
								$attach
							</div>
            			</div>
					</div>
            		<script>
							
					</script>
EOT;
			            			 
			            			return $content;
			            			 
			}, '点击查看');
			
			$grid->actions(function($actions){
				//$actions->append("<a href='javascript:;'  class='op'  data-type=''><i class='fa fa-eye'></i></a>");
				$actions->disableDelete();
   				$actions->disableEdit();
			});
				$grid->filter(function($filter)use($user){
					$filter->useModal();
					$filter->like('employee_name', '员工姓名');
                    if( $user->isRole('Administrator') ){
                        $filter->is('employee_id', '部门')->select(Department::where("id","<>","1")->get()->pluck('name', 'id'));
                    }

					$filter->between('created_at', '申请时间')->datetime();
					$filter->between('start_time', '假期开始时间')->datetime();
				});
				$grid->exporter(new VocationExport());
				$grid->disableCreation();
				
        });
    }
    protected function script()
    {
    	return <<<'EOT'
    		 window.audit = function(target){
    			var op = $(target).attr('data-op');
			    var id = $(target).attr('data-id');
    			var status =  $(target).attr('data-status');
	    		var res = 0;
    			var rr = $("#rejectReason_"+id).val();
    			console.log(rr);
	    		res = confirm("是否要进行操作？");
	    			if(res){
    					$.post('/admin/op',{id:id,status:status,op:op,rr:rr},function(data){
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
        return Admin::form(Vocation::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
