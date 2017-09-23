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
            vocations:[],
            currentPage:0,
            isEnd:false,
            loadMore:false,
        },
        methods:{   
        	getData:function(){
				$.showLoading();
				var param = {};
				if(this.currentPage!=""){
					param.page = this.currentPage;
				}
				var env = this;
				return $.get('{{ route('api_vocations') }}',param ).done(function(result){
						if(result.status==1000){
							var vos = result.playload.vocations;
							env.vocations = env.vocations.concat( vos.data );
							env.currentPage = vos.current_page;
							env.isEnd = vos.next_page_url?false:true;
						}
						  $.hideLoading();
						return true;
					}).fail(function (jqXHR, textStatus) {
							$.toast("休息一会","text");
						    $.hideLoading();
					}).always(function (data) {
						  $.hideLoading();
					});
			},  
        },
        mounted: function() {

       	 var detailContianer = document.querySelector(".scroll-container");
         var navObj = document.querySelector(".weui-panel__hd");
         var navObjHeight = navObj.offsetHeight;
			
         var height = document.documentElement.clientHeight;
         detailContianer.style.height = (height-navObjHeight) + "px";
            var env = this;
            this.getData();
            $(".scroll-container").infinite(15).on("infinite", function() {
                if(env.loadMore||env.isEnd) return;
                env.loadMore = true;
                env.currentPage  = env.currentPage+1;
                env.getData().then(function(result){
                    env.loadMore = false;
				});
            }); 
        }
    });

</script>
@endsection
@section('content')
 		<div class="weui-panel weui-panel_access"  v-cloak>
            <div class="weui-panel__hd">请假记录 <a href="{{ route('vocation') }}" class="weui-btn weui-btn_mini weui-btn_primary"  style="float:right;margin: -5px;">请假</a></div>
            <div class="weui-panel__bd  scroll-container">
            	@verbatim
            	<div class="weui-media-box weui-media-box_text"  v-for="v in vocations">
			        <h4 class="weui-media-box__title">{{v.rule.title}}   {{v.created_at}}</h4>
			        <p class="weui-media-box__desc">
			        事由：{{v.memo?v.memo:"无备注"}} <br />
			        {{(v.state==3&&v.result==2&&v.audit)?"被拒原因："+v.audit.memo:""}}
			        </p>
			        <ul class="weui-media-box__info">
			          <li class="weui-media-box__info__meta">{{v.vstate}}</li>
			          <li class="weui-media-box__info__meta">{{v.vresult}}</li>
			          <li class="weui-media-box__info__meta weui-media-box__info__meta_extra">{{v.start}} ({{parseInt(v.days)}}天)</li>
			        </ul>
			      </div>
                @endverbatim
                <div class="weui-infinite-scroll"  v-show="loadMore">
                    	  <div class="infinite-preloader"></div><!-- 菊花 -->
							  正在加载... <!-- 文案，可以自行修改 -->
                </div>
            </div>
        </div>
@endsection