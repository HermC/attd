@extends('layout.mobile')
@section('style')
@parent  
<style>
</style>
@endsection
@section('script')
@parent  
<!-- <script type="text/javascript" src="{{asset('m/js/plugin/city-picker.min.js')}}" ></script> -->
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript" charset="utf-8"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
        	memo:""
        },
        methods:{     
            operate:function(type){
					var param = {};
					param.status = type;
					var memo = $("#memo").val();
					if(memo==""&&type==2){
							$.toast("驳回时请填备注原因");
							return false;
					}else{
						param.memo = memo;
					}
					$.post("{{ route('vocation_audit',["id"=>$audit->id] ) }}",param).done(function(res){
						$.toast("审核完成","text");
						location.href="{!! route("msg",["type"=>"success","title"=>"审核完成","content"=>"此请假申请已经完成审核，请假人将看到结果"]) !!}";
					}).fail(function(res){
						$.toast("审核失败","text");
					}).always(function(res){
					});
            },
            preview:function(){
           		 @if( !empty($audit->vocation->attach))
	            	wx.previewImage({
	            	    current: '{{$audit->vocation->attach}}', // 当前显示图片的http链接
	            	    urls: ['{{$audit->vocation->attach}}'] // 需要预览的图片http链接列表
	            	});
            	@endif
            }
        },
        mounted: function() {

        	wx.config(<?php echo $js->config(array('previewImage'), false, true) ?>);
        	
        }
    });

</script>
@endsection
@section('content')
 		<div class="weui-panel">
            <div class="weui-panel__hd">请假信息</div>
            <div class="weui-panel__bd">
                <div class="weui-media-box weui-media-box_text">
                    <h4 class="weui-media-box__title">{{$audit->auditor->name}} 申请 {{$audit->vrule->title}},共{{ intval($audit->vocation->days) }}天</h4>
                    <p class="weui-media-box__desc">请假备注：{{ $audit->vocation->memo}} </p>
                    <ul class="weui-media-box__info">
                        <li class="weui-media-box__info__meta">申请时间：{{ $audit->vocation->created_at}}</li>
                    </ul>
					@if( !empty($audit->vocation->attach))
						<div>
							附件：<img src="/upload/{{$audit->vocation->attach}}"  style="" alt=""  @click="preview" />
						</div>
					@endif
					<div>
						请假历史统计: 已请事假{{$histories[1]}}天 ； 已请病假{{$histories[2]}}天 ；已请年休假 {{$histories[0]}}天
					</div>
                </div>

            </div>
        </div>
		<div class="weui-cells__title">历史请假统计</div>

        <div class="weui-cells__title">审核信息</div>
		<h5 ><span class="weui-cells__title">流程</span>：{{$audit->arule->title}}</h5>
		<h5><span class="weui-cells__title">进度</span>：
				<?php $ps = explode(",",$audit->arule->people); $people = $audit->arule->getAuditors($depart_id); $pos = array_search($audit->audit_user,$ps); ?>
				@foreach($ps as $p)
					@if( isset($people[$p]) )
						<span>{{$people[$p]}}
							@if( isset($audits[$p]) )
								({{$audits[$p]->getState()}})
							@else
								(未审核)
							@endif
						 </span>
				    @endif
				@endforeach
		</h5>
		@if($audit->result==0)
		<div class="weui-cells__title">备注</div>
		<div class="weui-cells weui-cells_form">
	      <div class="weui-cell">
	        <div class="weui-cell__bd">
	          <textarea class="weui-textarea"  name="memo"  id="memo"  placeholder="请输入备注" rows="3" ></textarea>
	          <div class="weui-textarea-counter">如果驳回，请务必填写备注</div>
	        </div>
	      </div>
	    </div>
	    <div class="button_sp_area"  style="    text-align: center;">
        	<a href="javascript:;" class="weui-btn weui-btn_mini weui-btn_primary"  @click="operate(1)">通过</a>
        	<a href="javascript:;" class="weui-btn weui-btn_mini weui-btn_default"   @click="operate(2)">驳回</a>
      </div>
      @endif
@endsection