<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
<meta name="format-detection" content="telephone = no" />
<meta name="msapplication-tap-highlight" content="no" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<link rel="stylesheet" href="//cdn.bootcss.com/weui/1.1.1/style/weui.min.css">
<link rel="stylesheet" href="//cdn.bootcss.com/jquery-weui/1.0.1/css/jquery-weui.min.css">
 @section('style')
<style>
[v-cloak] {
  display: none;
}
.scroll-container{
	  overflow: scroll;
	  position:relative
}
</style>
@show
</head>

<body>
    <!--app container start-->
    <div id="app">
    	@yield('content')
    </div>
    <!--<script src="//res.wx.qq.com/mmbizwap/zh_CN/htmledition/js/vconsole/2.5.1/vconsole.min.js"></script>-->
    <script src="//cdn.bootcss.com/vue/2.1.3/vue.min.js"></script>
    <script src="//cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/fastclick/1.0.6/fastclick.min.js"></script>
    <script src="//cdn.bootcss.com/lodash.js/4.17.2/lodash.js"></script>
    <script>
	 $(function() {
	        FastClick.attach(document.body);
	 });
	 $.ajaxSetup({
		 	headers: {
		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    },
	        error:function(response){
	         	$.hideLoading();
	            if(response.responseJSON){
	            	$.toast(response.responseJSON.msg,"text");	
	            }else{
	            	$.toast("系统错误","text");
	           }
	        },
	    }); 
	    Vue.mixin({
	  	  methods:{
		  		 	  	  	
	  	  }
	  });
    </script>
    <script src="//cdn.bootcss.com/jquery-weui/1.0.1/js/jquery-weui.min.js"></script>
    @section('script')
   	@show 
</body>

</html>
