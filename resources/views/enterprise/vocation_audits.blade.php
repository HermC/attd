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
            audits:[],
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
				return $.get('{{ route('api_vocation_audits') }}',param ).done(function(result){
						if(result.status==1000){
							var vos = result.playload.audits;
							env.audits = env.audits.concat( vos.data );
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
            <div class="weui-panel__hd">请假审核</div>
            <div class="weui-panel__bd  scroll-container">
            	@verbatim
            	<div class="weui-media-box weui-media-box_text"  v-for="au  in audits"  @click="window.location.href=au.url" >
			        <h4 class="weui-media-box__title">{{au.vocation.employee_name}}申请{{au.vrule.title}} {{au.vocation.days}}天 </h4>
			        <p class="weui-media-box__desc">
			           审核状态:  {{au.astate}} 
			        </p>
			        <ul class="weui-media-box__info">
			          <li class="weui-media-box__info__meta">{{au.updated_at}}</li>
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