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
class SystemSettingController extends Controller
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

            $content->header('系统设置');
            $content->description('设置列表');

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

            $content->header('系统设置');
            $content->description('编辑设置');

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

            $content->header('系统设置');
            $content->description('新增设置');
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

            $grid->id('ID')->sortable();
           
            $grid->disableCreation();
            //$grid->disableExport();
        });
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
            $form->text("key","系统键")->help("系统中索引，确保唯一不重复");
            $form->text("value","系统值");
            /* $form->embeds('value', function ($form) {
            	$form->text('extra1')->rules('required');
            	$form->email('extra2')->rules('required');
            	$form->mobile('extra3');
            	$form->datetime('extra4');
            	$form->dateRange('extra5', 'extra6', '范围')->rules('required');
            }); */
            $form->text("description","描述");
        });
    }
}
