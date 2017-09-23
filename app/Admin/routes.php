<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => "App\\Admin\\Controllers" ,
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

	$router->get('/', 'HomeController@index');
	$router->get('overview/reports/excel',  'StaticsController@overviewExcel')->name("overview_excel");
	$router->get('overview/reports',  'StaticsController@index');
	$router->get('overview/reports/monthly',  'StaticsController@monthly');
	$router->post('overview/reports/monthly',  'StaticsController@monthlyExcel');
	$router->get('api/reports', 'StaticsController@getOverviewReports')->name("overview_reports");
	$router->get('attendant/reports',  'StaticsController@index');
	$router->get('vocation/reports', 'StaticsController@vocationStatics');
	$router->post('api/synch', 'ApiController@syncRemote');
	$router->get('api/employee', 'ApiController@employee');
	$router->resource('enterprise/employee', EmployeeController::class);
	$router->resource('enterprise/departments', DepartmentController::class);
	$router->resource('attendants',  AttendantController::class );
	$router->resource('attendant/subjects', AttendantSubjectsController::class);
	$router->resource('attendant/rules', AttendantRulesController::class);
	$router->resource('vocations', VocationController::class);
	$router->resource('vocation/rules', VocationRuleController::class);
	$router->resource('audit', AuditController::class);
	$router->post('api/synch', 'ApiController@syncRemote');
	
	$router->post('op', 'OperateController@operate')->name("admin_op");
	
});
