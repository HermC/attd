<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Chart\Bar;
use Encore\Admin\Widgets\Chart\Doughnut;
use Encore\Admin\Widgets\Chart\Line;
use Encore\Admin\Widgets\Chart\Pie;
use Encore\Admin\Widgets\Chart\PolarArea;
use Encore\Admin\Widgets\Chart\Radar;
use Encore\Admin\Widgets\Collapse;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('控制面板');
            $content->description('');

            $content->row(function ($row) {
                /* $row->column(3, new InfoBox('今日已签到人数', 'users', 'aqua', '#', '113人'));
                $row->column(3, new InfoBox('今日迟到人数', 'shopping-cart', 'green', '#', '2'));
                $row->column(3, new InfoBox('今日早退人数', 'users','yellow', '#', '3'));
                $row->column(3, new InfoBox('外勤次数', 'file', 'red', '#', '22')); */
            });

        });
    }
}
