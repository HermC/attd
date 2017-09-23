<?php

namespace App\Http\Controllers;
use Stoneworld\Wechat\Server;


class WechatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        \Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        /* $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "欢迎关注 overtrue！";
        });

        Log::info('return response.');

        return $wechat->server->serve(); */

    	$appId  = config("ent.appId");
    	$secret = config("ent.secret");
    	$options = array(
    			'token'=>'l6Sbl9oIgVV1XNSk3RUpGsYDoWzh',   //填写应用接口的Token
    			'encodingaeskey'=>'C2WvxEa3JLcolK7QCpdTvAUQiT6Hj1UTMDKXGvvYqUu',//填写加密用的EncodingAESKey
    			'appid'=>$appId ,  //填写高级调用功能的appid
    			'appsecret'=>$secret, //填写高级调用功能的密钥
    			'agentid'=>'2', //应用的id
    	);
    	
    	$server = new Server($options);
    	
    	$server->on('message', function($message){
    		return "您好!";
    	});
    	
    	// 您可以直接echo 或者返回给框架
    	echo $server->server();
    	
    }
}