<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Route::get('/', function () {
    return view('welcome');
}); */

/* Route::group([
	'prefix'        => "ent",
	'namespace'     => Admin::controllerNamespace(),
	'middleware'    => ['web', 'admin'],
], function (Router $router) {

	$router->get('/', 'HomeController@index');

}); */
Route::any('/wechat', 'WechatController@serve');

Route::group([
	'prefix'        => "attendant",
	'middleware'    => ['web','webchat'], //,'webchat'
], function () {
	Route::get('/', 'EnterpriseController@index')->name("index");
    Route::get('/attendants', 'EnterpriseController@attendants')->name("attendants");//考勤记录
    Route::get('api/attendants', 'EnterpriseController@apiAttendants')->name("api_attendants");//考勤记录
    Route::get('/inout', 'EnterpriseController@attendant')->name("singinOrSignout");//签到试图
    Route::post('/inout', 'EnterpriseController@postAttendant')->name("singinOrSignout");//提交签到
    Route::get('/outdoors', 'EnterpriseController@outdoors')->name("outdoors");//外勤历史记录
    Route::get('api/outdoors', 'EnterpriseController@apiOutdoors')->name("api_outdoors");//外勤记录api
    Route::get('/outdoor', 'EnterpriseController@outdoor')->name("outdoor");//提交外勤表单
    Route::post('/outdoor', 'EnterpriseController@postOutdoor')->name("outdoor");//提交外勤
	Route::get('/statics', 'EnterpriseController@statics')->name("statics");
	Route::get('/rules', 'EnterpriseController@rules')->name("rules");
	Route::get('/vocations', 'EnterpriseController@vocations')->name("vocations");
	Route::get('/vocation', 'EnterpriseController@vocation')->name("vocation");
	Route::post('/vocation', 'EnterpriseController@postVocation');
	Route::post('/upload', [ 'uses'=>'EnterpriseController@postUpload'])->name("upload");
	Route::get('/vocation/audits', [ 'uses'=>'EnterpriseController@vocation_audits'])->name("vocation_audits"); //领导审核列表
	Route::get('/vocation/audit/{id}', 'EnterpriseController@vocationAudit')->name("vocation_audit");//请假记录
	Route::post('/vocation/audit/{id}', 'EnterpriseController@postVocationAudit')->name("vocation_audit");//请假记录
	
	Route::get('api/vocations', 'EnterpriseController@apiVocations')->name("api_vocations");//请假记录
	Route::get('api/vocation/audits', 'EnterpriseController@apiVocationAudit')->name("api_vocation_audits");//请假记录


	//Route::get('api/vocation/audits', 'EnterpriseController@apiVocations')->name("api_audit_vocations");//请假审核记录
	
});

/*Route::any('deploy', function()
{
    return Deeployer::run();
});*/

Route::get('/msg', 'HomeController@msg')->name("msg");

Route::post('/deploy', 'DeployController@hook')->name("hook");

//Route::get('/vocations', 'EnterpriseController@vocations');
/* Route::get('/', 'HomeController@getLandPage')->name('home'); */