<?php
/*
 * to-do:
 * 判断活动状态 
 * */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

 
class HomeController extends BaseController
{

	public function msg(){
		return response()->view("msg",[ "type"=>$this->data["type"],"title"=>$this->data["title"],"content"=>$this->data["content"], ] );
	}
	
}
