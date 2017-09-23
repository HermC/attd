@extends('layout.mobile')
@section('style')
@parent  
<style>
html,body,#allmap {width: 100%;overflow: hidden;margin:0;font-family:"微软雅黑";}
#div3 {
    background-color: #2a3138;
    width: 100%;
    height: 60px;
    position: fixed;
    bottom: 0;
    left: 0;
    text-align: center;
    font-size: 16px;
}
a.bottom_bar {
    background-color: #49b7e8;
    color: white;
    text-align: center;
    padding: .2em .3em;
    cursor: pointer;
    border-radius: .2em;
    display: block;
    float: right;
    margin: 10px 20px 10px 0;
    /* padding: 5px 0px 0px 0px; */
    width: 35%;
    height: 32px;
    line-height: 32px;
    color: #FFF;
    position: absolute;
    right: 0;
    text-decoration: none;
    /* font-size: 22px; */
    font-family: "Microsoft YaHei";
}
[v-cloak] {
  display: none;
}
</style>
@endsection
@section('script')
@parent  
<script type="text/javascript"   src="http://api.map.baidu.com/api?v=2.0&ak=8E6664daced3ba6e9176adbc0a9bd139" ></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript" charset="utf-8"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            'outdoorMemo':'',
            'latitude':'',
            'longitude':'',
            'location':"",
            'locations':[],
            'totalText':200,
        },
        computed:{
				positioned:function(){
					return this.latitude!='';	
				},
				curTextCount:function(){
					return this.outdoorMemo.length;
				}
        },
        methods:{
        	isLimited:function(){
				
            },
            submitOutdoor:function(){
            	var env = this;
              	 $.showLoading("提交中....");
              	 var param = {};
              	 param.userid = "{{$user->userid}}" ;
               	 param.gps = [env.longitude,env.latitude] ; //["118.74384557897","32.010468796943"];//
                 param.location = env.location ;;//env.location ;
				 if(param.location==""){
					 $.hideLoading();
					 $.toast("无外勤地址","text");
					 return false;
				 }
                 param.memo = env.outdoorMemo ;//"dddd" ;
				if(param.memo==""){
					$.hideLoading();
					$.toast("请填写备注","text");
					return false;
				}
      			$.post("{{ route('outdoor') }}" , param  , function(data){
					$.hideLoading();
					$.toast(data.msg);
      				if(data.status=1000){
						location.href="{!! route("msg",["type"=>"success","title"=>"提交成功","content"=>"已经成功提交外勤记录，请在规定时间内打卡签退"]) !!}";
					}

          		}).error(function(res){
          			$.hideLoading();
      				$.toast("提交失败:"+res.responseJSON.msg,"text");
              	});
            },    
            getPoisition:function(){
				
             }, 
            initMap:function(){
            	var env = this;
            	var map = new BMap.Map("map");  // 创建Map实例
            	map.centerAndZoom("南京",12);      // 初始化地图,用城市名设置地图中心点
            	map.disableDragging();
            	map.disableScrollWheelZoom();
            	map.disablePinchToZoom();
            	map.disableDoubleClickZoom();
            
            	wx.ready(function() {
            		// 1 判断当前版本是否支持指定 JS 接口，支持批量判断
            		wx.checkJsApi({
            			jsApiList : [ 'getLocation' ],
            			success : function(res) {
            				if(res['checkResult']['getLocation'] == false){
            					alert('抱歉，您的微信版本不支持定位，请升级到最新版本');
            				}
            			}
            		});

            		wx.getLocation({
            			success : function(res) {
            				//把获取到的坐标显示在地图上，并修改页面input值
            				showPoint(res.latitude, res.longitude);
            			},
            			cancel : function(res) {
            				alert('用户拒绝授权获取地理位置');
            			}
            		});

            	});
            	
            	$('#reLocateBtn').click(function(){
            		$('#reLocateBtn').val('定位中');
            		//showPoint(32.011809, 118.742826);
            		 wx.getLocation({
            			success : function(res) {
            				//把获取到的坐标显示在地图上，并修改页面input值
            				showPoint(res.latitude, res.longitude);
            			},
            			cancel : function(res) {
            				alert('用户拒绝授权获取地理位置');
            			}
            		});
            	});

            	wx.error(function(res) {
            		// config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
            		alert("签名过期!");
            	});

            	function showPoint(x, y) {
                	$.showLoading("定位中");
            		map.clearOverlays();
            		var ggPoint = new BMap.Point(y, x);

            		//坐标转换完之后的回调函数
            		translateCallback = function(data) {
            			$('#reLocateBtn').val("重新定位");
            			$.hideLoading();
            			if (data.status === 0) {
            				var mOption = {
            					poiRadius : 1000,
            					numPois : 50
            				};
            				var geocoder = new BMap.Geocoder();
            				
            				var myPoint = data.points[0];
            				//var pp = ["118.741694","32.010462"]; //测试用
            				//var myPoint = new BMap.Point(pp[0],pp[1]);//测试用
            				var mk = new BMap.Marker(myPoint);
            				map.addOverlay(mk);
            				map.centerAndZoom(myPoint, 17);
            				env.longitude  = myPoint.lng;
            				env.latitude= myPoint.lat;

            				geocoder.getLocation(
            					myPoint,
            					function showLabels(rs) {
            						var addComp = rs.addressComponents;
            						var address = addComp.city + addComp.district + addComp.street;
            						env.locations.push(address);
            						var allPois = rs.surroundingPois;
            						for (i = 0; i < allPois.length; ++i) {
            							env.locations.push(allPois[i].title);
            						};
            						env.location = address;
            					}, mOption);
            			}//data.status==0 end
            		}
            		setTimeout(function() {
            			var convertor = new BMap.Convertor();
            			var pointArr = [];
            			pointArr.push(ggPoint);
            			convertor.translate(pointArr, 1, 5, translateCallback)
            		}, 1000);
            	}
            	
            },//iniMap end
        },
        mounted: function() {

        	var titleHeight = document.querySelector(".weui-cells__title").offsetHeight;
            var cellsHeight = document.querySelector(".weui-cells").offsetHeight;
            var map1 = document.querySelector("#map");
   			
            var height = document.documentElement.clientHeight;
            map1.style.height = (height - titleHeight-cellsHeight) + "px";

            wx.config(<?php echo $js->config(array('getLocation'), false, true) ?>);
            
        	this.initMap();
     	
        }
    });

</script>
@endsection
@section('content')
 		<div class="bd"   v-cloak>
 			<div class="weui-cells__title">员工外勤 {{ date("Y-m-d") }}  {{ $user->name }}</div>
			<div class="weui-cells weui-cells_form">
			  <div class="weui-cell">
			    <div class="weui-cell__bd">
			      <textarea class="weui-textarea" placeholder="请输入外勤信息" rows="4"  @key.up="isLimited"   v-model="outdoorMemo" ></textarea>
			      <div class="weui-textarea-counter"><span>@{{curTextCount}}</span>/200 </div>
			    </div>
			  </div>
			</div>
			<div class="weui-cells__title">选择所在位置</div>
			<div class="weui-cells">
		      <div class="weui-cell weui-cell_select weui-cell_select-after">
		        <div class="weui-cell__hd">
		          <input type="button" value="定位" name="reLocateBtn" id="reLocateBtn" class="weui-btn weui-btn_mini weui-btn_primary"    />&nbsp;&nbsp;
		        </div>
		        <div class="weui-cell__bd">
		        	<select id="labelSelect"  class="weui-select"   v-model="location" >
						<option :value="loc"   v-for="loc in locations">@{{loc}}</option>
					</select>
		        </div>
		      </div>
		    </div>
 			<div id="map" ></div>
 		</div>
 		<div id="div3">
			<a href="#" class="bottom_bar" id="submitOutsideRecord" hidden="true" @click="submitOutdoor" >提交外勤单</a>
		</div>
@endsection