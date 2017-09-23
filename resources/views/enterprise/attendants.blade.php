@extends('layout.mobile')
@section('style')
@parent  
<style>
.weui-media-box__desc{
	    margin-bottom: 8px;
	    -webkit-line-clamp: 1;
}
</style>
@endsection
@section('script')
@parent  
<!-- <script type="text/javascript" src="{{asset('m/js/plugin/city-picker.min.js')}}" ></script> -->
<script>
    var app = new Vue({
        el: '#app',
        data: {
       	 attendants:[],
         currentPage:0,
         isEnd:false,
         loadMore:false,
        },
        methods:{     
        	reset:function(){
              		this.attendants = [];
					this.currentPage = 0;
					this.isEnd=false;
              },
              search:function(){
					this.reset();
					this.getData().then(function(){
						$.closePopup()
					});
					 
				},
        	getData:function(){
				$.showLoading();
				var param = {};
				var start = $("#start").val();
				if(start !=""){
					param.start = start;
				}
				var end = $("#end").val();
				if(end !=""){
					param.end = end;
				}
				if(this.currentPage!=""){
					param.page = this.currentPage;
				}
				var env = this;
				return $.get('{{ route('api_attendants') }}',param ).done(function(result){
						if(result.status==1000){
							var att = result.playload.attendants;
							env.attendants = env.attendants.concat( att.data );
							env.currentPage = att.current_page;
							env.isEnd = att.next_page_url?false:true;
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
               $("#start").calendar();
               $("#end").calendar();
              /*  var pull = $(".pull-wrap");
               pull.pullToRefresh(); 
               pull.on("pull-to-refresh", function() {
            	   //do something
                   env.reset();
                   env.getData().then(function(){
                	   pull.pullToRefreshDone();
                   });
            	   
            	}); */
        }
    });

</script>
@endsection
@section('content')
 		<div class="weui-panel">
        <div class="weui-panel__hd">考勤记录   <span  style="float:right;margin: -22px -10px -15px 0px;"><a href="{{ route('rules') }}"  class="weui-btn weui-btn_mini weui-btn_primary" >考勤</a>&nbsp;&nbsp;<a href="javascript:;" class="weui-btn weui-btn_mini weui-btn_primary  open-popup"  data-target="#search" >搜索</a></span></div>
        <div class="weui-panel__bd  scroll-container"  v-cloak>
        	<div class="pull-wrap">
        		<!-- <div class="weui-pull-to-refresh__layer">
				    <div class='weui-pull-to-refresh__arrow'></div>
				    <div class='weui-pull-to-refresh__preloader'></div>
				    <div class="down">下拉刷新</div>
				    <div class="up">释放刷新</div>
				    <div class="refresh">正在刷新</div>
				</div> -->
				@verbatim
		          <div class="weui-media-box weui-media-box_text"  v-for="att in attendants">
		            <h4 class="weui-media-box__title">{{ att.month }}月{{att.day}}日 星期{{ att.vweek }} {{ att.rule?att.rule.position:"" }}   </h4>
		            <p class="weui-media-box__desc"   v-for="log in att.logs">{{log.vtime}}   {{log.vstate}}    {{log.location}}</p>
		            <ul class="weui-media-box__info">
		             <!--  <li class="weui-media-box__info__meta">{{ att.vstate }}</li> -->
		              <li class="weui-media-box__info__meta">{{ att.vstate }}</li> 
		              <!-- <li class="weui-media-box__info__meta weui-media-box__info__meta_extra">其它信息</li> -->
		            </ul>
		          </div>
		          @endverbatim
		          <div class="weui-infinite-scroll"  v-show="loadMore">
		                 <div class="infinite-preloader"></div><!-- 菊花 -->
						正在加载... <!-- 文案，可以自行修改 -->
		        </div>
        	</div>
        </div>
      </div>
      <!--search bar-->
      <div id="search"  class="weui-popup__container">
		  <div class="weui-popup__overlay"></div>
		  <div class="weui-popup__modal">
		  		<div class="weui-cells__title">时间段搜索</div>
		  		<div class="weui-cells weui-cells_form">
				      <div class="weui-cell">
				        <div class="weui-cell__hd"><label for="date" class="weui-label">开始时间</label></div>
				        <div class="weui-cell__bd">
				          <input class="weui-input" id="start" type="text" readonly="">
				        </div>
				      </div>
				      <div class="weui-cell">
				        <div class="weui-cell__hd"><label for="date2" class="weui-label">结束时间</label></div>
				        <div class="weui-cell__bd">
				          <input class="weui-input" id="end" type="text" value="" readonly="">
				        </div>
				      </div>
				    </div>
				    <div class="action" style="padding:10px 15px 0px 15px;">
				    	<a href="javascript:;" class="weui-btn weui-btn_primary"  @click="search">搜索</a>
				    	<a href="javascript:;" class="weui-btn weui-btn_plain-primary  close-popup" >取消</a>
				    </div>
				    
		   		   <!-- <div class="weui-search-bar" id="searchBar">
				  <form class="weui-search-bar__form">
				    <div class="weui-search-bar__box">
				      <i class="weui-icon-search"></i>
				      <input type="search" class="weui-search-bar__input" id="searchInput" placeholder="搜索" required="">
				      <a href="javascript:" class="weui-icon-clear" id="searchClear"></a>
				    </div>
				    <label class="weui-search-bar__label" id="searchText">
				      <i class="weui-icon-search"></i>
				      <span>搜索</span>
				    </label>
				  </form>
				  <a href="javascript:" class="weui-search-bar__cancel-btn" id="searchCancel">取消</a>
				</div> -->
		  </div>
		</div>
 <!--search end-->
@endsection