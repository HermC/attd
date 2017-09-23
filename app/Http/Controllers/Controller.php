<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    /**
     * 系统初始化方法
     * @param Request $request
     * @author simon
     */
    public function __construct()
    {
    
    	/* //开启SQL记录，--临时
    	 DB::connection()->enableQueryLog(); */
    	$this->request = app('request');
    	$this->data =  $this->request->all();
    
    }
    
}
