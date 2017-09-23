<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Employee;
use App\Models\AttendantSubject;

class AttendantSubjectsController extends Controller
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

            $content->header('考勤科目管理');
            $content->description('考勤科目列表');

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

            $content->header('考勤科目管理');
            $content->description('编辑考勤科目');
			
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

            $content->header('考勤科目管理');
            $content->description('新增考勤科目');

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
        return Admin::grid(AttendantSubject::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->title('科目标题')->editable();
            $grid->disableExport();
            $grid->created_at('创建日期');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(AttendantSubject::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', '科目标题');
        });
    }
}
