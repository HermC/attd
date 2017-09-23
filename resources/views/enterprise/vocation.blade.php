@extends('layout.mobile')
@section('style')
@parent  
<style>
</style>
@endsection
@section('script')
@parent  
<script src="{{asset('js/jquery.ui.widget.js')}}"></script>
<script src="{{asset('js/jquery.iframe-transport.js')}}"></script>
<script src="{{asset('js/jquery.fileupload.js')}}"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            rules:{!!$rules->toJson()!!},
            curRule:"",
            type:1,
            sType:1,
            eType:1
        },
        methods:{
        	initFileUpload:function(id,url ,callback){
            	$('#'+id).fileupload({
               		autoUpload: true,
               		paramName:"file",
                    url:  url,
                    dataType: 'json',
                    done: function (e, data) {
                        //console.log(data);
                        $.hideLoading();
    	    				if(data._response.result){
    	    					var raw  = data._response.result;
    	    					if(typeof callback == "function"){
    	    						callback(raw);
        	    				}
    	    				}else{
								$.toast("传输失败");
        	    			}
                    },
                    start:function(e){
                    	$.showLoading();
                    },
                    progressall: function (e, data) {
                       /*  var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#progress .progress-bar').css(
                            'width',
                            progress + '%'
                        ); */
                    }
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
           },
            submit:function(){
            	var param = {};
				if(!(this.currentRule)){
					$.toast('请选择请假类型','text');
					return false;
				}
				param.vocation_rule_id=this.currentRule.id;
				var start_time= $("#start_time").val();
				if(start_time==""){
					$.toast('请选择起始时间','text');
					return false;
				}
				param.start_time = start_time;

				var sType= $("#sType").val();
				if(sType==""){
					param.sType = 1;
				}
				param.sType = sType;
				
				var end_time= $("#end_time").val();
				if( end_time=="" ){
					param.end_time = param.start_time;
				}
				param.end_time = end_time;
				var eType= $("#eType").val();
				if(eType==""){
					param.eType = 2;
				}
				param.eType = eType;
				/* var days= $("#days").val();
				
				if(days==""){
					$.toast('请选择起始时间','text');
					return false;
				} */
				/* if(this.type==1){
					var reg = new RegExp("^[0-9]*$");
					if(!reg.test(days)){
						$.toast('请输入正确天数','text');
						return false;
					}
					param.days = days;	
				}else{
					param.days = 0.5;	
				} */
				
				var memo= $("#memo").val();
				param.memo = memo;
				
				 var attach= $("#attach").val();
				if(this.currentRule.proof==2){	
					if(attach==""){
						$.toast('需要提交附件','text');
						return false;
					} 	
				}
				param.attach = attach;
				 
				 
				//param.vocation_rule_id = this.
				$.showLoading();
				$.post('{{route('vocation')}}',param,function(data){
					$.hideLoading();
					//$.toast("提交成功");
					 $.modal({
						  title: "提交结果",
						  text: "提交成功，接下来",
						  buttons: [
						    { text: "返回请假列表", onClick: function(){ 
						    	window.location.href="{{ route('vocations') }}";
							    }
						    },
						    { text: "继续添加", onClick: function(){ 
						    	window.location.reload();	
							 } },
						  ]
					}); 
				});
				
            }     
        },
        computed:{
			rulesNames:function(){
				var names = [];
				for(var i=0;i<this.rules.length;i++){
					names.push(this.rules[i].title);
				}
				return names;
			},
			currentRule:function(){
				var rule = _.find(this.rules,{"title":this.curRule});
				return rule;
			},
			needProof:function(){
				return true;
				//return this.currentRule && this.currentRule.proof==2;
			}
        },
        mounted: function() {
            var env = this;
            this.initFileUpload("uploaderInput", "{{  route('upload' , ['m'=>true]  ) }}"  ,function(data){
           		//env.uploadData.list.push(data.playload.url);
            	$(".weui-uploader__file").css( "background-image", 'url(' +data.playload.url+ ')' );
				$("#attach").val(data.playload.url);
            });
            $("#end_time").calendar({
      	    	minDate:"{{ date('Y-m-d') }}"
          	});
      	    $("#start_time").calendar({
      	    	minDate:"{{ date('Y-m-d') }}",
      	    	onChange:function(){
					
          	    }
          	});
        	
        	/*   $("#type").picker({
            	  title: "请选择时间类型",
            	  onChange:function(val){
    				console.log(val);
                  },
            	  cols: [
            	    {
            	      textAlign: 'center',
            	      values: [""]
            	    }
            	  ]
            }); */
           /*  $("#type").change(function(val){
					//console.log(val);
					$val = $(this).val();
					if($val!==1){
						
					}
            }) */;
        	$("#category").picker({
            	  title: "请选择请假类型",
            	  onChange:function(val){
					//console.log(val);
					env.curRule = val.value[0];
                  },
            	  cols: [
            	    {
            	      textAlign: 'center',
            	      values: env.rulesNames
            	    }
            	  ]
            });
        }
    });

</script>
@endsection
@section('content')
		<div class="weui-cells__title">事前请假 <a href="javascript: window.history.back() ;"  style="float:right;margin:-5px" class="weui-btn weui-btn_mini weui-btn_primary">返回</a></div>
 		<div class="weui-cells weui-cells_form">
 				<div class="weui-cell">
			        <div class="weui-cell__hd"><label for="name" class="weui-label">请假类型</label></div>
			        <div class="weui-cell__bd">
			          	<input class="weui-input" id="category"   type="text"   value=""  readonly="">
			        </div>
			    </div>
			    <!-- <div class="weui-cell weui-cell_select weui-cell_select-after">
			        <div class="weui-cell__hd">
			          <label for="" class="weui-label">时间类型</label>
			        </div>
			        <div class="weui-cell__bd">
			          	<select class="weui-select" name="type"  id="type"  v-model="type">
					            <option value="1">整天</option>
					            <option value="2">半天</option>
					    </select>
			        </div>
		      </div> -->
			  <div class="weui-cells__title">开始时间</div>
 			  <div class="weui-cell">
			        <div class="weui-cell__hd"><label for="time2" class="weui-label">选择时间</label></div>
			        <div class="weui-cell__bd">
			          	<input class="weui-input"  id="start_time"  name="start_time"   type="text"   value=""  readonly="">
			        </div>
		      </div>
		      <div class="weui-cell weui-cell_select weui-cell_select-after">
			        <div class="weui-cell__hd">
			          <label for="" class="weui-label">选择类型</label>
			        </div>
			        <div class="weui-cell__bd">
			          	<select class="weui-select" name="sType"  id="sType"  v-model="sType">
					            <option value="1">上午</option>
					            <option value="2">下午</option>
					    </select>
			        </div>
		      </div>
		      <div class="weui-cells__title">结束时间（不填即为请假半天）</div>
 			  <div class="weui-cell">
			        <div class="weui-cell__hd"><label for="time2" class="weui-label">选择时间</label></div>
			        <div class="weui-cell__bd">
			          	<input class="weui-input"  id="end_time"  name="end_time"   type="text"   value=""  readonly="">
			        </div>
		      </div>
		      <div class="weui-cell weui-cell_select weui-cell_select-after">
			        <div class="weui-cell__hd">
			          <label for="" class="weui-label">选择类型</label>
			        </div>
			        <div class="weui-cell__bd">
			          	<select class="weui-select" name="eType"  id="eType"  v-model="eType">
					            <option value="1">上午</option>
					            <option value="2">下午</option>
					    </select>
			        </div>
		      </div>
		      <!-- <div class="weui-cell">
			        <div class="weui-cell__hd"><label for="time2" class="weui-label">请假天数</label></div>
			        <div class="weui-cell__bd">
			          	<input class="weui-input"  id="days"  name="days"   type="text"   value=""   v-if="type==1"> 
			          	<span  v-if="type !=1">0.5天</span>
			        </div>
		      </div> -->
		      <div class="weui-cell">
		        <div class="weui-cell__bd">
		          	<textarea class="weui-textarea"  name="memo"  id="memo"  placeholder="请输入请假事由"  rows="3"></textarea>
		          <!-- <div class="weui-textarea-counter"><span>0</span>/200</div> -->
		        </div>
		      </div>
		      <div class="weui-cell"  v-show="needProof"  >
				    <div class="weui-cell__bd">
				      <div class="weui-uploader">
				        <div class="weui-uploader__hd">
				          <p class="weui-uploader__title">附件证据 </p>
				          <!-- <div class="weui-uploader__info">0/2</div> -->
				        </div>
				        <div class="weui-uploader__bd">
				          <ul class="weui-uploader__files" id="uploaderFiles">
				             <li class="weui-uploader__file weui-uploader__file_status" style="background-image:url(./images/pic_160.png)">
				              <!-- <div class="weui-uploader__file-content">50%</div> 
				              	<div class="weui-uploader__file-content">
				                	<i class="weui-icon-warn"></i>
				              </div>
				              -->
				              	<input type="hidden"  name="attach"   id="attach"   value=""  />
				            </li>
				          </ul>
				          <div class="weui-uploader__input-box">
				            	<input id="uploaderInput"  class="weui-uploader__input" type="file" accept="image/*" >
				          </div>
				        </div>
				      </div>
				    </div>
  				</div>
 		</div>
 		<a href="javascript:;" class="weui-btn weui-btn_primary"  style="margin:30px 20px;"  @click="submit">提交请假</a>
@endsection