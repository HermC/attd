<?php
namespace App\Services;
use Illuminate\Http\JsonResponse;
//use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Exception\HttpResponseException;
/* use Illuminate\Http\Illuminate\Http;
use User\Facades\Auth; */
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Response as HttpResponse;
use \Agent; 
use Symfony\Component\HttpKernel\Exception\HttpException;
use Qiniu\json_decode;
class Response  {
	
	/**
	 * 设置响应数据
	 * @param array $data
	 * 0 应用响应标识
	 * 1 HTTP响应标识
	 * 2 其它提示Msg标识
	 * @return Ambigous <NULL, unknown>
	 */
	protected function setResponseStatus(array $status)
	{
		
		//获取状态指定数，设置返回状态
		$statusNum = count($status);
		
		//设置默认值
		if ($statusNum === 1) 
		{
			$status[1] = $status[0];
			$status[2] = $status[0];
		} 
		elseif ($statusNum === 2) 
		{
			$status[2] = $status[1];
			$status[1] = $status[0];
		}else{
			//如果第三个有，说明是客户定制的 返回信息
			$status[3] = $status[2];
		} 
		
		
		return $status;
	}
	
	/**
	 * 设置需要返回的响应体
	 * 只需返回状态码和说明时，传入正常值
	 * 如：success,msg,success
	 * 当有值需要传出时，请在第一个参数传数组如：
	 * [success,msg,success],data
	 * @param string|array $appStatusCode
	 * @param string $statusMsg
	 * @param string $httpStatusCode
	 * @return array
	 */
	public function setResponseData(array $status,array $data = [])
	{
	
		$status = $this->setResponseStatus($status);
				
		$messages = [];
		$applicationCode = trans('code.a_'.$status[0]); //代表当前应用的错误码 是根据没个应用来定的
		$httpStatusCode = trans('code.h_'.$status[1]); // http返回的状态
		//set default message
		if($status[2] instanceof MessageBag){
				
				$returnMessage = $status[2]->first();

		}else{
				if(isset($status[3])) {
				
					//如果第4个有 ,并且不是mesagebag，说名不是系统信息，是客户定制的 返回信息
					$returnMessage = $status[3] ;
				
				}else{
					//使用默认的返回信息
					$returnMessage = trans('code.o_'.$status[2]);
				}	
		}
		
		//除了http头状态  还定义了一个应用状态  以方便应用适配
		$response = ['status'=>(int)$applicationCode];
		isset($returnMessage) && $response['msg'] = $returnMessage;
		$data && $response['playload'] = $data;
		
		/*
		 * $response 结构 status msg  data
		 * */
		return ['status'=>$httpStatusCode,'response'=>$response];
	}
	/**
	 * json快捷方式
	 * @param $status 数组 ['头部状态码','应用状态码','返回信息']  可以只写入第一个参数，后面使用code 配置文件中的信息 ，也可以同时赋值；字符串 必须为视图模板
	 * @param string|boolean $mixed 混合参数  ajax方式：此处必为数组，作为返回的playload； 当web访问，$status如果为数组，则认为是跳转回新页面并携带信息，此处必填必须跳转的url.,$status如果为字符串 ，此处为视图数据
	 * @param array $data 数据
	 * @param string $request
	 * @return \Illuminate\Http\JsonResponse|Ambigous <\Illuminate\View\View, mixed, \Illuminate\Foundation\Application, \Illuminate\Container\static>|Ambigous <\Illuminate\Http\$this, boolean, \Illuminate\HttRedirectResponse>
	 */
	public function json($flagsAndMsg,$mixed = null,array $data = [],$request = null){
		
		//快捷$data参数设置
		if (is_array($mixed) && empty($data))
		{
			$data = $mixed;
			$mixed = null;
		}
		
		empty($request) && $request = app('request');
		
		$response = $this->setResponseData($flagsAndMsg,$data);
			
		//设置header
		return new JsonResponse($response['response'],$response['status']);
		
	}

	/**
	 * 数据响应
	 * @param $status 数组 ['头部状态码','应用状态码','返回信息']  可以只写入第一个参数，后面使用code 配置文件中的信息 ，也可以同时赋值；字符串 必须为视图模板
	 * @param string|boolean $mixed 混合参数  ajax方式：此处必为数组，作为返回的playload； 当web访问，$status如果为数组，则认为是跳转回新页面并携带信息，此处必填必须跳转的url.,$status如果为字符串 ，此处为视图数据
	 * @param array $data 数据 
	 * @param string $request
	 * @return \Illuminate\Http\JsonResponse|Ambigous <\Illuminate\View\View, mixed, \Illuminate\Foundation\Application, \Illuminate\Container\static>|Ambigous <\Illuminate\Http\$this, boolean, \Illuminate\HttRedirectResponse>
	 */
	public function responsed($flagsAndMsg,$mixed = null,array $data = [],$request = null)
	{
		
		$device =  Agent::isMobile()? 'mobile':'web';
		
		//快捷$data参数设置
		if (is_array($mixed) && empty($data))
		{
			$data = $mixed;
			$mixed = null;
		}
		
		empty($request) && $request = app('request');
		
		if($request->ajax() || $request->wantsJson() ){
			
			if(!is_array($flagsAndMsg)  ){
				// ajax请求 同时返回的值第一个参数不是数组 是字符串，默认为片段视图名称
				// 直接输出 视图片段
				if(View::exists("{$device}.".$flagsAndMsg)){
					return view("{$device}.".$flagsAndMsg , $data );
				}else{
					if(!View::exists("web.".$flagsAndMsg)){
						abort(500, '无视图 无数组参数 出错');
					}else{
						return view("web.".$flagsAndMsg , $data );
					}
				}
			}
			
			if(!is_array($data)){
				// ajax请求 同时返回的值第一个参数不是数组 异常报错
				//abort(500, 'ajax请求时，respose服务第二参数只要有，必须为数组 作为返回值中的playload');
				$data = ["url"=>$data];
			}
			
			$response = $this->setResponseData($flagsAndMsg,$data);
			
			//设置header
			return new JsonResponse($response['response'],$response['status']);
			
		}else{
			
				//不是ajax请求 全部按照标准http页面请求
				
				if( is_string($flagsAndMsg) ){ //&& strpos($flagsAndMsg, '.') 一定要带点吗？ 有的试图就不带点
					
					//这里可以根据端的标识 选择模板 移动端？
					//	$view = trim(config('site.theme'),'.').'.';
					if(!is_array($data)){
						abort(500,"返回函数第一参数是字符串的情况下，默认为视图名称，必须指定第二参数为视图数据 数组");
					}
					
					//根据端的特征，返回对应的试图，先用全局控制器来控制  后面看看有没有更好的方案 比如boot函数
					if(  !View::exists("{$device}.".$flagsAndMsg) && $device == 'mobile'  ){
						//如果手机试图不存在，并且是手机访问，则回退
						$device = 'web';
			    	}
			    		
			    	return new HttpResponse(view("{$device}.".$flagsAndMsg,array_merge((array)$request->all(),$data)));
			    		
			   
			    	
				}
				if( is_array($flagsAndMsg) ){
					
	
					//如果是ajax请求，并且未指定模板  则跳转回上一个页面 并携带回转信息
					$response = $this->setResponseData($flagsAndMsg,$data);
					/* dd($response);
					$requestAuto = redirect()->back()->getRequest();
					if( $request->url() == $requestAuto->url() && $request->method() == $requestAuto->method()){
						//如果自动返回和请求一样，进入死循环 跳出
						//abort(500,"请求出现循环，请检查输入参数,一般是由于输入参数采用数组，期望返回ajax类型的 json结果，却使用的是web访问请求方式");
					   //直接输出json  web方式  一般调试 采用 否则一般不会出现
					   //建议方式 必须指定url
					   $isJson = json_decode($response);
					   if($isJson){
					   	return response()->json($response);
					   }else{
					   	return $response; 
					   } 
					   
					} */
					
					$redirect = empty($mixed) ? redirect()->back() : redirect($mixed);
					// 取消跳回上一个 必须明确指出
					//$redirect = redirect($mixed);
					$errors = $response['response']['status'] == 1000 ? [] : $response['response'];
					
					return $redirect->with('msg',$response['response']['msg'])->withInput(array_merge((array)$request->all(),$data))->withErrors($errors);
					
				}
				abort(500,'参数的数据类型错误，只有数组和字符串');
		}
	
	}
	
	/**
	 * 处理所有异常，并根据不同的异常，
	 * @param array $response
	 */
	public function error(array $response,$url = null) 
	{
		
		
	   $response = $this->setResponseData($response);
	   
	   $request = app('request');
	   
	   if($request->ajax() || $request->wantsJson())
	   {
		     	//如果 是ajax请求，返回错误json数据
				$throwResponse = new JsonResponse($response['response'],$response['status']);
	   }
	   else
	   {
	   	    // $errors 为status解析后的网站数据结构，有意义？
		   	$errors = $response['response']['status'] == 1000 ? [] : $response['response'];
	   		$redirect = empty($url) ? redirect()->back() : redirect($url);
	   		$throwResponse =  $redirect->with('msg',$response['response']['msg'])->withInput($request->all())->withErrors($errors);
	   }
	   
	   throw new HttpResponseException($throwResponse);
	}
	

}