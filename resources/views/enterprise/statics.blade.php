@extends('layout.mobile')
@section('style')
@parent  
<style>
	.title{
		height:50px;
		text-align: center;
		line-height: 50px;
		background: #fff;
	}
	.title .btn{
		float:right;
		margin-right: 10px;
		color: #999;
	}
</style>
@endsection
@section('script')
@parent  
<!-- <script type="text/javascript" src="{{asset('m/js/plugin/city-picker.min.js')}}" ></script> -->
<script type="text/javascript" src="{{ asset("js/vue-echarts.js") }}" ></script>

<script>
	Vue.component('chart', VueECharts)
    var app = new Vue({
        el: '#app',
        data: {
			persons:{},
        	static:{!! collect($data)->toJson() !!},
			title:"",
        	pie:{
				  title: {
				    text: '考勤饼状图',
				    left: '20%'
				  },
				  tooltip: {
				    trigger: 'item',
				    formatter: '{a} <br/>{b} : {c} ({d}%)' //a 人数 b类型 c数量 d百分百
				  },
				  legend: {
				    orient: 'horizontal',
				    bottom: '0',
				    left:'0',
				    data: ["正常","迟到","早退","缺勤","外勤"]
				  },
				  series: [
				    {
				      name: '人数',
				      type: 'pie',
				     // left:'0%',
				      radius: '40%',
				      center: ['28%', '50%'],
				      data:[ 	{value: {{$data["normal"]["count"]}} , name: '正常'},
						        {value: {{$data["late"]["count"]}}, name: '迟到'},
						        {value: {{$data["early"]["count"]}}, name: '早退'},
						        {value: {{$data["abcent"]["count"]}}, name: '缺勤'},
						        {value: {{$data["out"]["count"]}}, name: '外勤'}
						     ],
				      itemStyle: {
				        emphasis: {
				          shadowBlur: 10,
				          shadowOffsetX: 0,
				          shadowColor: 'rgba(0, 0, 0, 0.5)'
				        }
				      }
				    }
				  ]//series 
			}//pie
        },
        methods:{
			revealPeople:function(type){
				switch(type){
					case "normal":
							this.persons=this.static.normal.list;
							this.title="全勤人员详情";
						break;
					case "late":
						this.persons=this.static.late.list;
						this.title="迟到人员详情";
						break;
					case "early":
						this.persons=this.static.early.list;
						this.title="早退人员详情";
						break;
					case "abcent":
						this.title="缺勤人员详情";
						this.persons=this.static.abcent.list;
						break;
					case "out":
						this.title="外勤人员详情";
						this.persons=this.static.out.list;
						break;
				}
				$("#normallist").popup();
			}
        },
        mounted: function() {

        }
    });

</script>
@endsection
@section('content')
 		<div class="weui-panel ">
            <div class="weui-panel__hd">{{$type?"周":"日" }}统计报告</div>
            <div class="weui-panel__bd">
                <div class="chart"  style="width:100%">
                		<figure><chart :options="pie" ref="pie" auto-resize></chart></figure>
                </div>
                <div class="weui-cells">
		            <div class="weui-cells">
		            	<a class="weui-cell weui-cell_access " href="javascript:;" @click="revealPeople('normal')" >
			                <div class="weui-cell__bd">
			                    <p>正常签到</p>
			                </div>
			                <div class="weui-cell__ft">{{ count($data["normal"]["list"]) }}人</div>
			            </a>
			            <a class="weui-cell weui-cell_access" href="javascript:;"  @click="revealPeople('late')">
			                <div class="weui-cell__bd">
			                    <p>迟到人数</p>
			                </div>
			                <div class="weui-cell__ft">{{ count($data["late"]["list"]) }}人</div>
			            </a>
			            <a class="weui-cell weui-cell_access" href="javascript:;"  @click="revealPeople('early')">
			                <div class="weui-cell__bd">
			                    <p>早退人数</p>
			                </div>
			                <div class="weui-cell__ft">{{ count($data["early"]["list"]) }}人</div>
			            </a>
			            <a class="weui-cell weui-cell_access" href="javascript:;"  @click="revealPeople('abcent')">
			                <div class="weui-cell__bd">
			                    <p>缺勤人数</p>
			                </div>
			                <div class="weui-cell__ft">{{ count($data["abcent"]["list"]) }}人</div>
			            </a>
			            <a class="weui-cell weui-cell_access" href="javascript:;"  @click="revealPeople('out')">
			                <div class="weui-cell__bd">
			                    <p>外勤人数</p>
			                </div>
			                <div class="weui-cell__ft">{{ count($data["out"]["list"]) }}人</div>
			            </a>
			        </div>
		        </div>
            </div>
        </div>
		<div id="normallist" class="weui-popup__container">
			<div class="weui-popup__overlay"></div>
			<div class="weui-popup__modal">
				<div class="title">
					<span v-text="title">人员详情</span>
					<a c href="javascript:;" class="btn close-popup">关闭</a>
				</div>
				<div class="weui-cells">
					<div class="weui-cell" v-for=" (count,name) in persons">
						<div class="weui-cell__bd">
							<p>@{{name}}</p>
						</div>
						<div class="weui-cell__ft">@{{count}}天</div>
					</div>
				</div>
			</div>
		</div>
@endsection