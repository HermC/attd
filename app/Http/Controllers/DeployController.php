<?php
/*
 * to-do:
 * 判断活动状态 
 * */
namespace App\Http\Controllers;
use Illuminate\Routing\Controller as Basic;
use Log;


class DeployController extends Basic
{
	public function hook(){
        error_reporting(1);
        //需要自动部署的项目目录bbbb
        $dir =  '/data/wwwroot/attendance';

        //coding填写的令牌（在第六点配置，防止别人恶作剧）
      $token = 'att#endance%2017$';

      //验证令牌
        $json = json_decode(file_get_contents('php://input'), true);
        if (empty($json['token']) || $json['token'] !== $token) {
            throw new \ErrorException("远程pull请求参数不正确");
        }

        //这里因为我的git不支持直接git pull，所以带上了远程库名和分支，'2>&1'是让执行管道输出结果。
        $result =  shell_exec("cd /data/wwwroot/attendance && /usr/bin/git reset --hard && /usr/bin/git pull 2>&1");
        echo $result;
    }

}
