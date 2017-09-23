<?php

use App\Admin\Extensions\BaiduMap;
use Encore\Admin\Grid\Column;
use App\Admin\Extensions\Column\ExpandRow;
/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Column::extend('expand', ExpandRow::class);
Encore\Admin\Form::extend('baiduMap', BaiduMap::class);
Encore\Admin\Form::forget(['map', 'editor']);

Admin::js('//cdn.bootcss.com/vue/2.1.3/vue.min.js');
Admin::js('//cdn.bootcss.com/lodash.js/4.17.4/lodash.min.js');
Admin::js('https://cdn.bootcss.com/echarts/3.5.4/echarts.min.js');
Admin::js('https://cdn.bootcss.com/layer/3.0.1/layer.js');
function script()
{
	$token = csrf_token();
	return <<<EOT
		$(function(){
				if(document.body.clientWidth<1400){
						$(".sidebar-mini").addClass("sidebar-collapse");
				}
		});
		$.ajaxSetup({
		    headers: {
		        'X-CSRF-TOKEN': '$token'
		    }
		});

EOT;
    }
    
Admin::script(script());