@extends('layout.mobile')
@section('style')
@parent  
<style>
</style>
@endsection
@section('script')
@parent  
<!-- <script type="text/javascript" src="{{asset('m/js/plugin/city-picker.min.js')}}" ></script> -->
<script>
    var app = new Vue({
        el: '#app',
        data: {
        },
        methods:{     
        },
        mounted: function() {
        }
    });

</script>
@endsection
@section('content')
 		<header class="demos-header">
	      <h1 class="demos-title">企业应用</h1>
	    </header>
	    <div class="weui-cells">
		  <a class="weui-cell weui-cell_access" href="{{ route('rules') }}">
		    <div class="weui-cell__bd">
		      <p>签到</p>
		    </div>
		    <div class="weui-cell__ft">
		    </div>
		  </a>
		  <a class="weui-cell weui-cell_access" href="{{ route('attendants') }}">
		    <div class="weui-cell__bd">
		      <p>我的签到</p>
		    </div>
		    <div class="weui-cell__ft">
		    </div>
		  </a>
		  <a class="weui-cell weui-cell_access"    href="{{ route('outdoor') }}">
		    <div class="weui-cell__bd">
		      <p>外勤</p>
		    </div>
		    <div class="weui-cell__ft">
		    </div>
		  </a>
		   <a class="weui-cell weui-cell_access" href="{{ route('vocations') }}">
		    <div class="weui-cell__bd">
		      <p>请假</p>
		    </div>
		    <div class="weui-cell__ft">
		    </div>
		  </a>
		  <a class="weui-cell weui-cell_access"    href="{{ route('vocation_audits') }}">
		    <div class="weui-cell__bd">
		      <p>请假审核</p>
		    </div>
		    <div class="weui-cell__ft"></div>
		  </a>
		</div>
@endsection