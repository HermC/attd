@extends('layout.mobile')
@section('style')
@parent  
<style>
html,body,#allmap {width: 100%;overflow: hidden;margin:0;font-family:"微软雅黑";}
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
            point : null,
            map:null,
            position:[{{ !isset($in)?"": $in->longitude.",".$in->latitude }}],
            signoutPosition:[{{ !isset($out)?"": $out->longitude.",".$out->latitude }}],
            distance:{{$rule->dist_span}},
            target:[{{$rule->longitude}},{{$rule->latitude}}],
            calDistance:100000,
            calOutDistance:100000,
            isSingin: {{isset($in)?"true":"false"}},
            isSingout:{{isset($out)?"true":"false"}},
        },
        computed:{
			isPositioned:function(){
				return this.position.length>0;
			},
			canSin:function(){
				return this.isPositioned && this.calDistance<= (this.distance+10);
			}
        },
        methods:{
            getLevel:function(distance){
				var level = 16;
             	 switch(distance){
			      	    case 100:
			      	    case 200:
			      	 	case 500:
			          	 	level = 16;
			      	 	case 1000:
			          	 	level = 15;
			      	 	case 2000:
			          	 	level = 14; 	  
			      	 }
		      	return level;
            },     
            displayDistance:function(){
                @if( !isset($in) || (isset($in) && $in->type!=3))
                	if(this.position.length!=0){
    	            	var pos =  this.position; 
    					var pointB = new BMap.Point(pos[0],pos[1]);
    	      			this.calDistance = this.map.getDistance(this.point,pointB).toFixed(2);
    	      			this.makePointAndLable(pointB,"签到位置距目标："+this.calDistance+"米");
    				    var polyline = new BMap.Polyline([this.point,pointB], {strokeColor:"blue", strokeWeight:6, strokeOpacity:0.5 });  //定义折线
    					this.map.addOverlay(polyline);     //添加折线到地图上
                	}
                @endif
                @if( !isset($in) || (isset($out) && $in->type!=3))
					if(this.signoutPosition.length!=0){
						var pos2 =  this.signoutPosition; 
						var pointC = new BMap.Point(pos2[0],pos2[1]);
		      			this.calOutDistance = this.map.getDistance(this.point,pointC).toFixed(2);
		      			this.makePointAndLable(pointC, "签退位置距目标："+this.calOutDistance+"米");
					    var polyline2 = new BMap.Polyline([this.point,pointC], {strokeColor:"green", strokeWeight:6, strokeOpacity:0.5 });  //定义折线
						this.map.addOverlay(polyline2);     //添加折线到地图上
					}
                @endif
            },
            getPosition:function(){
            	this.map.clearOverlays();
            	this.makeTarget();
                var env = this;
                if(this.position.length==0 || this.signoutPosition.length==0){
                	//调用百度 或者微信 gis
                	$.showLoading("定位中...");
                	wx.getLocation({
            			success : function(res) {
            				//把获取到的坐标显示在地图上，并修改页面input值
            				$.hideLoading();
            				var ggPoint = new BMap.Point(res.longitude,res.latitude);
            				//坐标转换完之后的回调函数
            				var convertor = new BMap.Convertor();
                			var pointArr = [];
                			pointArr.push(ggPoint);
                			convertor.translate(pointArr, 1, 5, function(data) {
                    			$.hideLoading();
                    			//console.log(data);
                    			if (data.status === 0) {
                         			var myPoint = data.points[0];
                    				//var pp = ["118.741694","32.010462"]; //测试用
                    				//var myPoint = new BMap.Point(pp[0],pp[1]);//测试用
                         			$.toptip('定位成功', 'success');
            						if(!env.isSingin){
            							env.position =  [myPoint.lng,myPoint.lat];
            						}
            						if(env.isSingin&&!env.isSingout){
            							env.signoutPosition =  [myPoint.lng,myPoint.lat];
            						}
            						env.displayDistance();
                    			}//data.status==0 end
                    		});
            			},
            			cancel : function(res) {
            				alert('用户拒绝授权获取地理位置');
            			}
            			
            		});    	
                }else{
                	this.displayDistance();
                }
            },
            makeTarget:function(){
           	 	 this.point = new BMap.Point(this.target[0],this.target[1]);  // 创建点坐标A--大渡口区
	        	 var point = this.point;
	        	 var level = this.getLevel();
	        	 this.map.centerAndZoom(point, level);
	        	var circle = new BMap.Circle(point,500,{strokeColor:"red" , strokeWeight:2, strokeOpacity:0.5}); //创建圆
	        	 this.map.addOverlay(circle); 
	        	this.makePointAndLable(point,"500m范围内可签到");
             },
            makePointAndLable:function(point,title){
            	var marker = new BMap.Marker(point);// 创建标注
            	this.map.addOverlay(marker);             // 将标注添加到地图中
            	marker.disableDragging();           // 不可拖拽
            	var opts = {
          			  position : point,    // 指定文本标注所在的地理位置
          			  offset   : new BMap.Size(0, -60)    //设置文本偏移量
          	     }
          		var label = new BMap.Label(title, opts);  // 创建文本标注对象
          				label.setStyle({
          					 color : "red",
          					 fontSize : "12px",
          					 height : "20px",
          					 lineHeight : "20px",
          					 fontFamily:"微软雅黑"
          				 });
          			this.map.addOverlay(label); 
             },
             operation:function(type){
				//检测是否存在当前位置，
  				//this.getPosition();
    			var env = this;
            	 $.showLoading("提交中....");
            	 var param = {};
            	 param.userid = "{{$user->userid}}" ;
            	 if(type==1){
            		 param.gps = env.position;
            		 param.type = "in";
          		}else{
          			 param.gps =  env.signoutPosition;
          			 param.type = "out";
              	}
    			$.post("{{ route('singinOrSignout') }}" , param  , function(data){
    				$.hideLoading();
    				$.toast(data.msg);
    			    if(type==1){
    			    	env.isSingin = true;
    			    	location.href="{!! route("msg",["type"=>"success","title"=>"签到成功","content"=>"已经在签到地点打卡成功，请在规定时间内打卡签退"]) !!}";
            		}else{
            			env.isSingout = true;
            			location.href="{!! route("msg",["type"=>"success","title"=>"签退成功","content"=>"已经在签退地点打卡成功，请注意可重复签退"]) !!}";
                	}
    			    env.getPosition();
        		});
             },
        },
        mounted: function() {

        	var titleHeight = document.querySelector(".weui-cells__title").offsetHeight;
            var cellsHeight = document.querySelector(".weui-cells").offsetHeight;
            var map1 = document.querySelector("#allmap");
   			
            var height = document.documentElement.clientHeight;
            map1.style.height = (height - titleHeight-cellsHeight) + "px";
            
        	this.map = new BMap.Map("allmap");
        	var map = this.map;
        	//map.centerAndZoom("南京",15);  //初始化地图,设置城市和地图级别。

        	
			var env = this;
			
			wx.config(<?php echo $js->config(array('getLocation'), false, false) ?>);
			
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
				env.getPosition();
           });
        	//this.getPosition();
      		
      		
      			
        }
    });

</script>
@endsection
@section('content')
 		<div class="bd"   v-cloak>
 			<div class="weui-cells__title">员工考勤 {{ date("Y-m-d") }}  {{ $user->name }}</div>
 			<div class="weui-cells"  >
 					<div class="weui-cell">
 						<div class="weui-cell__bd">
 							<h3>{{$rule->position}}</h3>
 						</div>
 					</div>
 					<div class="weui-cell">
			            <div class="weui-cell__bd">
			              <p>签到 <span>{{$rule->sigin_time}}</span></p>
			            </div>
			            <div class="weui-cell__ft">
			               @if( isset($in) && $in->type==3)
			               		<span>已出外勤</span>
			               @endif
			               @if( isset($in) && $in->type==1)
			               		<span>{{$in->getVresultAttribute()}}</span>
			               @endif
			            	@if(!isset($in))
				            	<a href="javascript:;" class="weui-btn weui-btn_mini weui-btn_primary"  @click="operation(1)"  v-show="canSin&&!isSingin">签到</a>
			               @endif
			            </div>
			        </div>
			        <div class="weui-cell">
			            <div class="weui-cell__bd">
			              <p>签退 <span>{{$rule->sigout_time}}</span></p>
			            </div>
			            <div class="weui-cell__ft">
			            	  @if( isset($out) )
			            	  	 @if( $out->type==3 )
			            	  	 <span>已出外勤</span>
			            	  	 @else
			            	  	 <span>上次签退{{ $out->created_at->format('H:i') }} </span>&nbsp;
			            		@endif
			            	@endif
			            	<a href="javascript:;" class="weui-btn weui-btn_mini weui-btn_primary"  @click="operation(2)"  v-show="canSin">签退</a>
			            </div>
			        </div>
 			</div>
 			<div id="allmap"></div>
 		</div>
@endsection