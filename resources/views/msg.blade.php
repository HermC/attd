@extends('layout.mobile')
@section('style')
@parent  
<style>
</style>
@endsection
@section('script')
@parent  
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript" charset="utf-8"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
        },
        methods:{
            closeWindow:function(){
            	wx.closeWindow();
            }     
        },
        mounted: function() {
        	wx.config(<?php echo $js->config(array('closeWindow'), false, true) ?>);
        }
    });

</script>
@endsection
@section('content')
 		<div class="weui-msg">
		      <div class="weui-msg__icon-area"><i class="{{  $type=='error'? 'weui-icon-warn' : 'weui-icon-success' }} weui-icon_msg"></i></div>
		      <div class="weui-msg__text-area">
		        <h2 class="weui-msg__title">{{$title}}</h2>
		        <p class="weui-msg__desc">{{$content}}</p>
		      </div>
		      <div class="weui-msg__opr-area">
		        <p class="weui-btn-area">
		          <a href="javascript:;" class="weui-btn weui-btn_primary" @click=" closeWindow">关闭</a>
		          <!-- <a href="javascript:;" class="weui-btn weui-btn_default">辅助操作</a> -->
		        </p>
		      </div> 
		      <div class="weui-msg__extra-area">
		        <div class="weui-footer">
		          <p class="weui-footer__links">
		            <a href="javascript:void(0);" class="weui-footer__link">南京质检院</a>
		          </p>
		          <p class="weui-footer__text">Copyright © 2107</p>
		        </div>
		      </div>
    	</div>
@endsection