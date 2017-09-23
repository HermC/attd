<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model as BaseModel;
//use Croppa;

 class Model extends BaseModel {

	/**
	 * 时间戳格式
	 * @var mixed
	 */
	//protected $dateFormat = 'U';
	
	/**
	 * 黑名单 为空则表示关闭
	 * @var array
	 */
	protected $guarded = [];
	
	
	/* public  function getThumb($picUrl,$width=null,$height=null){
		return Croppa::url( upload_path($picUrl)  , $width ,  $height  );
	} */
	

	/* public function avatar($logo){
		$r = str_contains($logo, "http");
		if($r){
			return $logo;
		}else{
			return empty($logo)?"/images/defaultavatar.jpg": upload_path($logo);
		}
	} */

}