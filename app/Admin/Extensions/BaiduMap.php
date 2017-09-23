<?php
namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\PlainInput;

class BaiduMap extends Field
{
	
	protected $view = 'admin.baidumap';

	protected $column = [];
	
	protected static $css = [
	   // 'http://api.map.baidu.com/res/20/bmap_autocomplete.css',
	];

	protected static $js = [
		//'http://api.map.baidu.com/api?v=2.0&ak=Zg0BowzTAlAaDvXz8bSpLr4N',
		//'http://api.map.baidu.com/getscript?v=2.0&ak=Zg0BowzTAlAaDvXz8bSpLr4N&services=&t=20170502192300'
		"http://api.map.baidu.com/api?v=2.0&ak=8E6664daced3ba6e9176adbc0a9bd139"
	];

	public function __construct($column, $arguments)
	{
		$this->column['latitude'] = $column;
		$this->column['longitude'] = $arguments[0];
		$this->column['position'] = $arguments[1];
		
		array_shift($arguments);
		array_shift($arguments);
	
		$this->label = $this->formatLabel($arguments);
		$this->id = $this->formatId($this->column);

	}
	
	public function render()
	{

		/* $this->prepend('<i class="fa fa-pencil"></i>')
		->defaultAttribute('type', 'text')
		->defaultAttribute('id', $this->id)
		->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
		->defaultAttribute('value', old($this->column, $this->value()))
		->defaultAttribute('class', 'form-control dropdown-input '.$this->getElementClassString())
		->defaultAttribute('placeholder', $this->getPlaceholder()); */
		 
		$position = old($this->column['position'], $this->value['position']);
		$latitude = old($this->column['latitude'], $this->value['latitude']);
		$latitude = !empty($latitude)?$latitude:"";
		$longitude = old($this->column['longitude'], $this->value['longitude']);
		$longitude = !empty($longitude)?$longitude:"";
		$this->script = <<<EOT
				//初始化百度地图
				// 百度地图API功能
					function G(id) {
					return document.getElementById(id);
				}
				var lon ='{$longitude}';
				var lat ='{$latitude}';
				var map = new BMap.Map("l-map");
				map.centerAndZoom("南京",12);                   // 初始化地图,设置城市和地图级别。
				
				if(lon!=""&&lat!=""){
					var point = new BMap.Point(lon,lat);
					
					var marker = new BMap.Marker(point);// 创建标注
	            	map.addOverlay(marker);             // 将标注添加到地图中
	            	marker.disableDragging();           // 不可拖拽
	            	var opts = {
	          			  position : point,    // 指定文本标注所在的地理位置
	          			  offset   : new BMap.Size(0, -60)    //设置文本偏移量
	          	     }
	          		var label = new BMap.Label("当前签到位置：{$position}", opts);  // 创建文本标注对象
	          				label.setStyle({
	          					 color : "red",
	          					 fontSize : "12px",
	          					 height : "20px",
	          					 lineHeight : "20px",
	          					 fontFamily:"微软雅黑"
	          				 });
	          			map.addOverlay(label); 
				}
				
				
				var ac = new BMap.Autocomplete(    //建立一个自动完成的对象
					{"input" : "{$this->id["position"]}"
					,"location" : map
				});
				
				ac.setInputValue("{$position}");
				
				ac.addEventListener("onhighlight", function(e) {  //鼠标放在下拉列表上的事件
				var str = "";
					var _value = e.fromitem.value;
					var value = "";
					if (e.fromitem.index > -1) {
						value = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
					}
					str = "FromItem<br />index = " + e.fromitem.index + "<br />value = " + value;
		
					value = "";
					if (e.toitem.index > -1) {
						_value = e.toitem.value;
						value = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
					}
					str += "<br />ToItem<br />index = " + e.toitem.index + "<br />value = " + value;
					G("searchResultPanel").innerHTML = str;
				});
		
				var myValue;
				ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
				var _value = e.item.value;
					myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
					G("searchResultPanel").innerHTML ="onconfirm<br />index = " + e.item.index + "<br />myValue = " + myValue;
		
					setPlace();
				});
		
				function setPlace(){
					map.clearOverlays();    //清除地图上所有覆盖物
					function myFun(){
						var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
						map.centerAndZoom(pp, 18);
						map.addOverlay(new BMap.Marker(pp));    //添加标注
				  		document.getElementById("longitude").value = pp.lng;
						document.getElementById("latitude").value = pp.lat;
					}
					var local = new BMap.LocalSearch(map, { //智能搜索
					  onSearchComplete: myFun
					});
					local.search(myValue);
				}
EOT;
		
		return parent::render();//->with([])

	}
	
}