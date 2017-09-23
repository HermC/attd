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
 		<div class="weui-panel weui-panel_access">
            <div class="weui-panel__hd">规则列表</div>
            <div class="weui-panel__bd">
                <div class="weui-media-box weui-media-box_text">
                    <h4 class="weui-media-box__title">标题一</h4>
                    <p class="weui-media-box__desc">由各种物质组成的巨型球状天体，叫做星球。星球有一定的形状，有自己的运行轨道。</p>
                </div>
                <div class="weui-media-box weui-media-box_text">
                    <h4 class="weui-media-box__title">标题二</h4>
                    <p class="weui-media-box__desc">由各种物质组成的巨型球状天体，叫做星球。星球有一定的形状，有自己的运行轨道。</p>
                </div>
            </div>
        </div>
@endsection