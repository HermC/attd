<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as Basic;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Encryption\DecryptException;
use Crypt;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Stoneworld\Wechat\Js;

class BaseController extends Basic
{	
		
	    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	   
	    protected  $globalData = [];
	   
		protected  $config = null;
		/**
		 * 通用结构返回体
		 * @var string
		 */
		protected  function getResultStruct($result=false ,$msg="",$data=null){
			$struct = new \stdClass();
			$struct->result = $result;
			$struct->msg = $msg;
			$struct->data = $data;
			return $struct;
		}
		
		//用与 json合并 全局数据
		protected  function getsonResult($localData=[]){
			
			return array_merge($localData,$this->globalData);
			
		}
		
		/**
	     * 系统初始化方法
	     * @param Request $request
	     * @author simon
	     */
	    public function __construct()
	    {
	    	
	    	$this->request = app('request');
	    	
	    	$this->data =  $this->request->all();
	    	

	    	$js = new Js( config("ent.appId")  , config("ent.secret") );
	    	
	    	view()->share('js',$js);
	    	//$js->config(array $APIs, $debug = false, $beta = false, $json = true, $isAdminGroup = false);
	    	/* $config = Cache::get('site:config',function(){
	    		return  Config::all('name','value')->pluck('value', 'name')->toArray();
	    	});

    		$acat = Cache::get('activity:category',function(){
    			return   ActivityCategory::roots()->get(["name","id"]);
    		}); */


	       /* if( strpos(Agent::getUserAgent(), 'MicroMessenger') !== false ){
	       
		       	$wechat = app('wechat');
		       
		       	$js = $wechat->js;
		       
		       	$jsStr = $js->config(array('onMenuShareTimeline', 'onMenuShareAppMessage'), false);
		       	
		       	view()->share('jsStr',$jsStr);
		       	
		       	$this->globalData[ 'jsStr']=$jsStr;
	       	 
	       } */

	    }
	    
	    /**
	     * 直接返回json
	     * 参数传递只是为了设置响应体，即$this->setResponseData
	     * @param array|string $status api|ajax类型，则返回json否则返回view
	     * @param array $data
	     * @throws JsonResponse
	     */
	    protected function json($status,$url = null,array $data = [])
	    {
	    	return app('App\Services\Response')->json($status,$url,$data,$this->request);
	    }
	    
	    /**
	     * 直接返回响应快捷方式
	     * 参数传递只是为了设置响应体，即$this->setResponseData
	     * @param array|string $status api|ajax类型，则返回json否则返回view
	     * @param array $data
	     * @throws JsonResponse
	     */
	    protected function response($status,$url = null,array $data = [])
	    {
	    	return app('App\Services\Response')->responsed($status,$url,$data,$this->request);
	    }
	    
	    /**
	     * 抛出异常
	     * @param unknown $response
	     * @author simon
	     */
	    protected function throwError(array $response,$url = null)
	    {
	    	return app('App\Services\Response')->error($response,$url);
	    }
	    /**
	     * 快捷验证方法
	     * @param array $rules
	     * @param array $messages
	     * @param array $customAttributes
	     * @param array $data 传入自定义数据验证
	     * @author simon
	     */
	    
	    /**
	     * 错误统一处理
	     * @param unknown $response
	     * @author simon
	     */
	    protected function error(array $response,$url = null)
	    {
	    	return app('App\Services\Response')->error($response,$url);
	    }
	    /**
	     * 快捷验证方法
	     * @param array $rules
	     * @param array $messages
	     * @param array $customAttributes
	     * @param array $data 传入自定义数据验证
	     * @author simon
	     */
	    protected function validate(array $rules, array $messages = [], array $customAttributes = [],$url = null,$data=null)
	    {
	    	 
	    	if (is_string($messages))
	    	{
	    		$url = $messages;
	    		$messages = [];
	    	}
	    	if(isset($data)){
	    
	    		$validator = $this->getValidationFactory()->make($data, $rules, $messages, $customAttributes);
	    		 
	    	}else{
	    
	    		$validator = $this->getValidationFactory()->make($this->request->all(), $rules, $messages, $customAttributes);
	    		 
	    	}
	    	 
	    	if ($validator->fails())
	    	{
	    		//dd($validator->messages());
	    		return $this->throwError(['error','error',$validator->messages()],$url);
	    	}
	    	 
	    	return $validator;
	    }

	     
	    /**
	     * 验证图片
	     * simon后期修改
	     * @param string $img
	     * @param array $extensions
	     * @param number $maxSize
	     * @throws HttpResponseException
	     * @return boolean
	     */
	     
	    protected  function validateFile($file,array $extensions = [],$maxSize = 0){
	    
	    	//set default
	    	$limit = is_numeric($maxSize)&&$maxSize!=0 ? $maxSize : config("app.file_size");
	    	$extensions = empty($extensions) ? config("app.file_extend") : $extensions;
	    	 
	    	//验证文件大小
	    	$size = $file->getClientSize();
	    	if($size > $limit ){
	    		return $this->throwError(['error','error',"超过文件大小"]);
	    		//return  $this->getResultStruct(false,"超过文件大小");
	    	}
	    
	    	//验证文件后缀名
	    	$extend = $file->getClientOriginalExtension();
	    	$extend = strtolower($extend);
	    	if( ! in_array( $extend ,$extensions)   ){
	    		return $this->throwError(['error','error',"上传文件非法"]);
	    		//return  $this->getResultStruct(false,"上传文件非法");
	    	}
	    	 
	    	return true;
	    	//return $this->getResultStruct(true);
	    }
	     
	    /**
	     * 文件上传
	     * @param string $name
	     * @param string $path
	     * @param array  $extensions
	     * @param number $maxSize
	     * @return string|NULL
	     */
	    protected function uploadFile($name,$extensions = [],$path = null,$maxSize = 0)
	    {
	    	 
	    	empty($path) && $path = '/uploads/'.date('Ym').'/';
	    	 
	    	//上传Logo
	    	if ($this->request->hasFile($name) && $this->request->file($name)->isValid())
	    	{
	    		//验证
	    		$this->validateFile($this->request->file($name),$extensions,$maxSize);
	    		 
	    		//filepath
	    		$extension = $this->request->file($name)->getClientOriginalExtension();
	    		$filename = uniqid().".{$extension}";
	    		 
	    		$this->request->file($name)->move(public_path($path),$filename);
	    		return $path.$filename;
	    	}
	    	 
	    	return null;
	    }
	     
	    /**
	     * 快捷处理文件方法
	     * @param string $img
	     * @param string $hash
	     * @throws HttpResponseException
	     * @return \App\Http\Controllers\Controller
	     */
	     
	    protected  function saveFile($img,$savePath,$imgName=null,$thumb=false,$dataSet=null)
	    {
	    	 
	    	$type =gettype($img);
	    	//dump($type);die();
	    	switch($type){
	    		case 'string':
	    			if(isset($dataSet)&&isset($dataSet[$img])){
	    				 
	    				//如果包含数据集 则从数据集中获取
	    
	    				$file  =  $dataSet[$img];
	    
	    			}elseif ($this->request->hasFile($img) && $this->request->file($img)->isValid())
	    			{
	    				$file =  $this->request->file($img);
	    				 
	    			}else{
	    				//不存在返回空字符串
	    				return "";
	    			}
	    			break;
	    		case 'object':
	    			$file = $img;
	    			break;
	    			 
	    	}
	    	
	        $this->validateFile($file);
	    	 	 
	    	$extension = $file->getClientOriginalExtension();
	    
	    	if($imgName){
	    		$filename = $imgName."_". time()."{$extension}";
	    	}else{
	    		$filename = uniqid().".{$extension}";
	    	}
	    
	    	$file->move(public_path($savePath),$filename);
	    
	    	//$relativePath = $savePath.$filename;
	    
	    	if($thumb){
	    		//生成缩略图
	    	}
	    
	    	return  $filename;
	    	 
	    }
	 

	    /* 上传片段*/
	    public  function postUpload(){
	    	$isNeedMov = isset($this->data['m']) ? $this->data['m'] : true;
	    	//$user_id = session("user_session")->id;
	    	//路径相对于根目录 public 配置文件指向 的这个  filsystem指定了根目录
	    	$base = config('app.upload_dir');
	    	
	    	if($isNeedMov){
	    		$uploadPath = "/{$base}/temp/";
	    	}else{
	    		$uploadPath = "/{$base}/".date('Ym').'/';
	    	}
	    	
	    	$filename = $this->saveFile("file", $uploadPath);
	    	$url = $uploadPath.$filename;
	    	return $this->json(["success","success","提交成功"],['url'=>$url]);
	    
	    }
	    
}
