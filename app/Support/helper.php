<?php
/**
 * 工具函数，可迁移
 */
	/**
	 * @desc  im:十进制数转换成三十六机制数
	 * @param (int)$num 十进制数
	 * return 返回：三十六进制数
	 */
	function get_10_36($num)
	{
		$num = intval($num);
		if ($num <= 0)
		{
			return false;
		}
		
		$charArr = array('0','1','2','3','4','5','6','7','8','9','A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$char = '';
		do
		{
			$key = ($num - 1) % 36;
			$char= $charArr[$key] . $char;
			$num = floor(($num - $key) / 36);
		} while ($num > 0);
		
		return $char;
	}
	
	/**
	 * @desc  im:三十六进制数转换成十机制数
	 * @param (string)$char 三十六进制数
	 * return 返回：十进制数
	 */
	function get36_10($char)
	{
		$array=array('0','1','2','3','4','5','6','7','8','9','A', 'B', 'C', 'D','E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O','P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z');
		$len=strlen($char);
		$sum = 0;
		for($i=0;$i<$len;$i++)
		{
			$index=array_search($char[$i],$array);
			$sum+=($index+1)*pow(36,$len-$i-1);
		}
		return $sum;
	}
	
	/**
	 * IP转为数值型
	 * @param string $ip
	 * @return string
	 */
	function ip_long($ip)
	{
		return sprintf("%u",ip2long($ip));
	}
	
	/**
	 * 数值转为IP
	 * @param numeric $proper_address
	 * @return string
	 */
	function long_ip($proper_address)
	{
		return long2ip($proper_address);
	}
	
	/**
	 * 字符串转换成16进制
	 * @param string $str
	 * @return string
	 */
	function str_to_hex($str)
	{
		$m1=$url="";
		for($i=0;$i<=strlen($str);$i++){
			$m1=base_convert(ord(substr($str,$i,1)),10,16);
			$m1!="0" &&	$url=$url."\x".$m1;
		}
		return $url;
	}
	
	/**
	 * 16进制转换成UTF-8字符串
	 * @param unknown $hex
	 * @return Ambigous <mixed, string>
	 */
	function hex_to_str($hex)
	{
		$string = str_replace(array("\t","\n","\r"),'',stripcslashes($hex));
		// 		$encode = mb_detect_encoding($string, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		// 		if ($encode != 'UTF-8') {
		// 			$string = iconv($encode, 'UTF-8', $string);
		// 		}
		return trans_coding($string);
	}
	
	/**
	 * curl操作
	 * @param string $url
	 * @param array $options
	 * @return mixed
	 */
	function curl($url,array $options = [])
	{
		$ch = curl_init($url);
	
		$defaultptions = [
				CURLOPT_TIMEOUT => 1,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_USERAGENT=> 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36',
				CURLOPT_FOLLOWLOCATION => true,//抓取跳转后的页面
		];
	
		if (empty($options))
		{
			$options = $defaultptions;
		}
		else
		{
			$newKey = array_keys($options);
			foreach ($defaultptions as $key=>$value)
			{
				if (!in_array($key, $newKey,true))
				{
					$options[$key] = $value;
				}
			}
		}
	
		curl_setopt_array($ch,$options);
	
		$result = curl_exec($ch);
	
		curl_close($ch);
	
		return $result;
	}
	
	/**
	 * 创建目录
	 * @param string $dir
	 * @param number $mode
	 * @return boolean|bool
	 */
	function mk_dir($dir, $mode = 0755)
	{
		if (is_dir($dir) || @mkdir($dir, $mode)) return true;
		if (!@mk_dir(dirname($dir), $mode)) return false;
		return mkdir($dir, $mode);
	}
	
	/**
	 * 删除文件或目录
	 * @param string $path
	 */
	function remove_dir($path)
	{
		$path = rtrim($path,'/');
		if (is_dir($path))
		{
			$dirArr = scandir($path);
			foreach ($dirArr as $dirVal)
			{
				if ($dirVal === '.' || $dirVal === '..') continue;
				$filePath = $path.'/'.$dirVal;
				is_dir($filePath) ? remove_dir($filePath) : @unlink($filePath);
			}
		}
		else
		{
			return @unlink($path);
		}
		chmod($path, 0777);
		return @rmdir($path);
	}
	
	/**
	 * 得到一个文件或目录的大小
	 * @param string $path
	 */
	function dir_size($path)
	{
		if (is_dir($path))
		{
			$size = 0;
			$dirArr = scandir($path);
			foreach ($dirArr as $dirVal)
			{
				if ($dirVal === '.' || $dirVal === '..') continue;
				$filePath = "{$path}/{$dirVal}";
				$size += is_dir($filePath) ? dir_size($filePath) : filesize($filePath);
			}
		}
		else
		{
			$size = filesize($path);
		}
		return $size;
	}
	
	/**
	 * 将非UTF-8字符集转为UTF-8
	 * @param string $string
	 * @return string
	 */
	function trans_coding($string,$encode = 'UTF-8')
	{
		$getEncode = mb_detect_encoding($string, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
		if ($getEncode != $encode)
		{
			$string = iconv($getEncode,$encode,$string);
		}
		return $string;
	}

	/**
	 *
	 * 单位转换，字节转换为常用单位量
	 * @param numeric $size => Beat
	 * @return string
	 */
	function unit_conversion($size,$delimiter = '')
	{
		return byte_size($size,$delimiter);
	}
	
	/**
	 * 字节转化为大小
	 * @param numeric $byte
	 * @param string $delimiter
	 * @return string
	 */
	function byte_size($byte,$delimiter = '')
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		for ($i = 0; $byte >= 1024 && $i < 6; $i++) $byte /= 1024;
		return round($byte, 2) . $delimiter . $units[$i];
	}
	
	/**
	 *
	 * 文件大小转换为字节
	 * @param numeric $size => Beat
	 * @return numeric
	 */
	function size_byte($size)
	{
		
		if(is_numeric($size)) return $size;
		
		//获取单位
		$unit = strtoupper(substr($size,-2,2));
		//获取数值
		$size = rtrim($size,$unit);
		
		switch($unit)
		{
			case 'KB' : $realSize = $size * pow(2,10); break;
			case 'MB' : $realSize = $size * pow(2,20); break;
			case 'GB' : $realSize = $size * pow(2,30); break;
			case 'TB' : $realSize = $size * pow(2,40); break;
			case 'PB' : $realSize = $size * pow(2,50); break;
			default	  : $realSize = 0;
		}
		return $realSize;
	}
	
	/**
	 *
	 * 人民币转换 
	 * @param numeric $size => Beat
	 * @return numeric
	 */
	 function price_format($price)
	{
	    $price_label = $price;
	    if ($price > 100000000) {
	        /*  if (($price / 100000000) == intval($price / 100000000)) {
	         $price_label = ($price / 100000000) . ' 亿';
	        } */
	        $price_label = ($price / 100000000) . ' 亿元';
	    } else if ($price > 10000) {
	        /*   if (($price / 10000) == intval($price / 10000)) {
	         $price_label = ($price / 10000) . ' 万';
	        } */
	        $price_label = round(($price / 10000),2) . ' 万元';
	    }else{
	        return $price_label=$price. ' 元';
	    }
	    return $price_label;
	}
	
	/**
	 * 静态资源
	 * @param unknown $file
	 * @param string $url
	 * @return string
	 */
	function static_asset($file = null,$url = null)
	{
	
		if (empty($url) && empty(env('cdn_url')))
		{
			$path = dirname(str_replace(env('DOCUMENT_ROOT'),'' , env('SCRIPT_FILENAME')));
			$url = '/'.ltrim(ltrim($path,'/'),'\\');
		}
	
		$url = rtrim($url,'/');
		!empty($file) && $file = ltrim($file,'/');
	
		return "{$url}/{$file}";
	}
	
	/**
	 * 日期格式
	 * @param int $timestamp
	 * @param number $type
	 * @return string
	 */
	function format_date($timestamp,$type = 2)
	{
		switch($type)
		{
			case 1:
				$format = 'Y/m/d';
				break;
			case 2:
				$format = 'Y/m/d H:i';
				break;
			case 3:
				$format = 'Y/m/d H:i:s';
				break;
			case 4:
				$format = 'H:i:s';
				break;
		}
		return date($format,$timestamp);
	}

	/**
	 * 脚本目录路径
	 * @param string $path
	 * @return string
	 */
	function script_path($path = null)
	{
		$scriptPath = dirname(public_path()).'/scripts';
		return empty($path) ? $scriptPath : $scriptPath.'/'.ltrim($path);
	}
	
	/**
	 * 上传目录路径 基于public访问根路径
	 * @param string $path
	 * @return string
	 */
	function upload_path($path = null)
	{
		if($path){
			if(starts_with($path, '/')){
				return "/upload". $path;
			}else{
				return "/upload/". $path;
			}
			
		}
		return "/upload/";
	}
	
	/**
	 *  生成指定长度的随机字符串(包含大写英文字母, 小写英文字母, 数字)
	 *
	 * @author Wu Junwei <www.wujunwei.net>
	 *
	 * @param int $length 需要生成的字符串的长度
	 * @return string 包含 大小写英文字母 和 数字 的随机字符串
	 */
	function random_str($length)
	{
		//生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
		$arr = array_merge(range(1, 9)); //, range('a', 'z'), range('A', 'Z')
	
		$str = '';
		$arr_len = count($arr);
		for ($i = 0; $i < $length; $i++)
		{
		$rand = mt_rand(0, $arr_len-1);
		$str.=$arr[$rand];
		}
	
		return $str;
}
	
	//  生成礼品卡订单号
	function createGiftNo()
	{
		return 'G'.date('Ymdhis', time()).substr(floor(microtime()*1000),0,1).rand(0,9);
	}

	//  生成导出报名表名称
	function createortFileName()
	{
		return 'E'.date('Ymdhis', time()).substr(floor(microtime()*1000),0,1).rand(0,9);
	}
	

	//  生成支付号号
	 function createPayid()
	{
		return 'P'.date('Ymdhis', time()).substr(floor(microtime()*1000),0,1).rand(0,9);
	}
	
   function perfessions(){
   	//"学生","IT","摄影师","设计师","公务员","教师","律师","医生","白领","会计","工程师","艺术家","文学家","公务员","体育人士","企业老板","个体户","离休干部","其他"
   	return [
				   	1=> "学生",
				   	2=>"IT",
				   	3=>"摄影师业",
				   	4=>"设计师",
				   	5=>"公务员",
				   	6=>"教师",
				   	7=>"律师",
				   	8=>"医生",
				   	9=>"白领",
				   	10=>"会计",
				   	11=>"工程师",
				   	12=>"艺术家",
				   	13=>"文学家",
				   	14=>"公务员",
				   	15=>"体育人士",
				   	16=>"企业老板",
				   	17=>"个体户",
				   	18=>"离休干部",
				   	19=>"其他",
   			];
   }
   
   //获取年龄层次
   function ages(){
				   	//行业数据
				   	return [
					    1=> "16岁以下",
					    2=>"16—25岁",
					    3=>"25—35岁",
					    4=>"35—50岁",
					    5=>"50岁以上",
   					];
   		
   }
   
   function getValuesFromSets($keys,$sets){
   		$newsets = [];
   		foreach ($keys as $k){
   			if(isset($sets[$k])){
   				$newsets[] = $sets[$k];
   			}
   		}
   		$t = implode(',', $newsets);
   		return implode(',', $newsets);
   }
  
   function implodeSets($sets){
   	
   	  $newsets = implode("|", $sets);
   		if($newsets){
   			return "|".$newsets."|";
   		}else{
   			return "";
   		}
   		
   }
   function explodeSets($sets){
   	
   	    $newstes = explode( "|" , substr($sets,1,strlen($sets)-2 ));
   	    $newstes = is_array($newstes)?$newstes:[];
   		return $newstes;
   }
	//获取行业
	function industries(){
		//行业数据
		return [
					1=> "农、林、牧、渔业",
					2=>"采矿业",
					3=>"制造业",
					4=>"电力、燃气及水的生产和供应",
					5=>"建筑业",
					6=>"交通运输、仓储和邮政",
					7=>"信息传输、计算机服务和软件业",
					8=>"批发和零售",
					9=>"住宿和餐饮业",
					10=>"金融业",
					11=>"房地产业",
					12=>"租赁和商务服务",
					13=>"科学研究、技术服务和地质勘查",
					14=>"水利、环境和公共设施管理",
					15=>"居民服务和其他服务",
					16=>"教育",
					17=>"卫生、社会保障和社会福利",
					18=>"文化、体育和娱乐业",
					19=>"公共管理和社会组织",
					20=>"国际组织"
				];
		 
	}
	
/**
 * 计算两个坐标之间的距离(米)
 * @param float $fP1Lat 起点(纬度)
 * @param float $fP1Lon 起点(经度)
 * @param float $fP2Lat 终点(纬度)
 * @param float $fP2Lon 终点(经度)
 * @return int
 */
function distanceBetween($fP1Lat, $fP1Lon, $fP2Lat, $fP2Lon){
    $fEARTH_RADIUS = 6378137;
    //角度换算成弧度
    $fRadLon1 = deg2rad($fP1Lon);
    $fRadLon2 = deg2rad($fP2Lon);
    $fRadLat1 = deg2rad($fP1Lat);
    $fRadLat2 = deg2rad($fP2Lat);
    //计算经纬度的差值
    $fD1 = abs($fRadLat1 - $fRadLat2);
    $fD2 = abs($fRadLon1 - $fRadLon2);
    //距离计算
    $fP = pow(sin($fD1/2), 2) +
          cos($fRadLat1) * cos($fRadLat2) * pow(sin($fD2/2), 2);
    return intval($fEARTH_RADIUS * 2 * asin(sqrt($fP)) + 0.5);
}
/**
 * 百度坐标系转换成标准GPS坐系
 * @param float $lnglat 坐标(如:106.426, 29.553404)
 * @return string 转换后的标准GPS值:
 */
function BD09LLtoWGS84($fLng, $fLat){ // 经度,纬度
    $lnglat = explode(',', $lnglat);
    list($x,$y) = $lnglat;
    $Baidu_Server = "http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x={$x}&y={$y}";
    $result = @file_get_contents($Baidu_Server);
    $json = json_decode($result);
    if($json->error == 0){
        $bx = base64_decode($json->x);
        $by = base64_decode($json->y);
        $GPS_x = 2 * $x - $bx;
        $GPS_y = 2 * $y - $by;
        return $GPS_x.','.$GPS_y;//经度,纬度
    }else
        return $lnglat;
}

function getWeek($day){
	switch ($day){
		case 1:
			return "星期一";
		case 2:
			return "星期二";
		case 3:
			return "星期三";
		case 4:
			return "星期四";
		case 5:
			return "星期五";
		case 6:
			return "星期六";
		case 0:
			return "星期日";
	}
}

function sc_send(  $text , $desp = '' , $key = 'SCU7866T5950d161702faeea56d0dd743c7fbd1258fff7f90b04b'  )
{
    $postdata = http_build_query(
        array(
            'text' => $text,
            'desp' => $desp
        )
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context  = stream_context_create($opts);
    return $result = file_get_contents('https://sc.ftqq.com/'.$key.'.send', false, $context);

}