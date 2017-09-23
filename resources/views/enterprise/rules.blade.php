@extends('layout.mobile')
@section('style')
@parent  
<style>
[v-cloak] {
  display: none;
}
</style>
@endsection
@section('script')
@parent  
<script type="text/x-template" id="rule-detail">
	@verbatim
		<a    class="weui-cell weui-cell_access" href="javascript:;">
            <div class="weui-cell__bd">
              <p>{{ rule.position }}</p>
              <p>有效距离{{ rule.dist_span  }} </p>
            </div>
            <div class="weui-cell__ft">{{rule.distance}}m</div>
          </a>
@endverbatim
</script>
<script type="text/javascript"   src="http://api.map.baidu.com/api?v=2.0&ak=8E6664daced3ba6e9176adbc0a9bd139" ></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript" charset="utf-8"></script>
<script>
   var map = new BMap.Map("allmap");
   var convertor = new BMap.Convertor();
    var app = new Vue({
        el: '#app',
        data: {
            rules:{!! $rules !!},
            position:{ }
        },
        /* components:{
			'rule':{
				props: {
				  	rule:Object
				  },
				  data:function(){
					  	return {
					  		pos:null,
					  		bd:null
					  		//isUpdatingPosition:false
					  	};
				 },
				  template: '#rule-detail',
				  methods:{
					caculateDistance:function(p1,p2){
						var pos =  this.position; 
						var pointA = new BMap.Point(p1[0],p1[1]);
    					var pointB = new BMap.Point(p2[0],p2[1]);
    	      			this.calDistance = map.getDistance(pointA,pointB).toFixed(2);
					}
				  },
				  computed: {
					  distance:function(){
					  		
					  	}
				  },
				  events: {
					    'update-distance': function (pos) {
					      // 事件回调内的 `this` 自动绑定到注册它的实例上
					      this.$set('pos' , pos); 
					    }
				 },
				  ready:function(){

				  }
			}
        }, */
        computed:{
			
        },
        methods:{   
        	getUserLoction:function(){
		  		var self = this;
		  		var geolocation = new BMap.Geolocation();
		  		geolocation.getCurrentPosition(function(r){
		  			if(this.getStatus() == BMAP_STATUS_SUCCESS){
		  				/* var mk = new BMap.Marker(r.point);
		  				map.addOverlay(mk);
		  				map.panTo(r.point); */
		  				alert('您的位置：'+r.point.lng+','+r.point.lat);
		  			}
		  			else {
		  				alert('failed'+this.getStatus());
		  			}        
		  		},{enableHighAccuracy: true})
				
				//self.$broadcast("updating-distance");	

		  	},
        },
        beforeMount:function(){
        	$.showLoading("定位中....");
        },
        mounted: function() {
            
            var env = this;
            
        	wx.config(<?php echo $js->config(array('getLocation'), false, true) ?>);
        	wx.ready(function() {
        		//env.getUserLoction();
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
        				//console.log('test1');
        				env.position.latitude = res.latitude;
        				env.position.longitude = res.longitude;
        				var ggPoint = new BMap.Point(res.longitude,res.latitude);
        				var pointArr = [];
                		pointArr.push(ggPoint);
        				convertor.translate(pointArr, 1, 5, function(data){
        					var pointA =  data.points[0];
                			//计算距离
            				//循环所有规则
            				var rules = env.rules;
            				//console.log(env);
	        				for(var i = 0;i<rules.length;i++){
	                			var rule = rules[i];
	                			var point = new BMap.Point(rule.longitude,rule.latitude);
	                			rule.distance = map.getDistance(pointA,point).toFixed(2);
	                			Vue.set(env.rules, i, rule);
	                			//console.log('test');
	            			}
	        				$.hideLoading();
	        				//env.rules = rules;
                		});
        				
        			},
        			cancel : function(res) {
        				alert('用户拒绝授权获取地理位置');
        				$.hideLoading();
        			}
        		});

        	});
        }
    });

</script>
@endsection
@section('content')
 		<div class="weui-cells__title">签到地点</div>
 		<div class="weui-cells"   v-cloak>
 		@verbatim
          <a v-for="rule in rules"   class="weui-cell weui-cell_access"   :href="rule.url"   v-show="rule.distance < rule.dist_span" >
            <div class="weui-cell__bd">
              <p>{{ rule.position }}</p>
              <p>有效打卡距离{{ rule.dist_span }} </p>
            </div>
            <div class="weui-cell__ft">{{rule.distance}}m</div>
          </a>
          @endverbatim
        </div>
@endsection