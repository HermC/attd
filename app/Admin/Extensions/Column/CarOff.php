<?php

namespace App\Admin\Extensions\Column;
use Encore\Admin\Facades\Admin;
//use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Carbon\Carbon;
use App\Models\Entry;

class CarOff extends AbstractDisplayer
{
	
    public function display(\Closure $callback = null, $btn = '')
    {
    	
        $callback = $callback->bindTo($this->row);
		
        //dump( ceil(1.1) );
        //dd($this->row->car_type);
        $entry =  Entry::where("uuid",$this->row->uuid)->first();
    		$ct = $entry->carType;
    		$cartype = $ct['title'] ;
    		$yj = $ct['yj'] ;
    		$gb =$ct['gb'] ;
    		$freeTime = $ct['freeTime'] ;
    		$zn = $ct['zn'] ;
        
        $entryTime = new Carbon($this->row->entry_time);
        $offTime = Carbon::now();
        $minutes = $offTime->diffInMinutes($entryTime,true);
        
        $stageMinute = 60;//计费步骤 
       
    if($minutes>$freeTime){
        	$feeTime = $minutes - $freeTime;
        	$feeMinutes = ceil($feeTime/$stageMinute);
        }else{
        	$feeTime = 0;
        	$feeMinutes = 0 ;
        }
        
        $eid = $this->row->id;
        $timeFee = $feeMinutes*$zn ; //超出时间的费用
        
        $total = $timeFee + $gb;
        
        $state = call_user_func($callback);

        $key = $this->getKey();

        $name = $this->column->getName();

        Admin::script($this->script());
        if($state==2){
        	return "已离场";
        }
        $op = "";
        if(Admin::user()->can('off-assert-altprice')){
        	$op .= "<p><span>手动更新</span>&nbsp;&nbsp;&nbsp;<input type='text' value='' id='finalprice_{$eid}'  placeholder='修改价格' />元  （填写此处后，此价格将为最终总额（请自行将工本费考虑在内，否则统计数据将不准）</p><p><span>备注原因:&nbsp;&nbsp;&nbsp;</span><textarea name='memo_{$eid}' id='memo_{$eid}' cols='30' rows='2'></textarea></p>";
        }
        return <<<EOT
  <span>未离场</span>&nbsp;&nbsp;
<button class="btn btn-xs btn-default grid-open-map" data-key="{$key}"  data-toggle="modal" data-target="#grid-modal-{$name}-{$key}">
    <i class="fa fa-check"></i> 立刻出场
</button>

<div class="modal" id="grid-modal-{$name}-{$key}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">汽车出场</h4>
      </div>
      <div class="modal-body">
        <div  style="height:450px;">
        		<h1>车辆类型：$cartype</h1>
        	    <h3>计费方式： 押金 {$yj}元   工本费 {$gb}元   免费时间: {$freeTime}分钟  滞纳金: {$zn}元/$stageMinute 分钟  </h3>
        	    <h3>明细： 免费时间 {$freeTime}   计费时间 {$feeTime}分钟    工本费 {$gb}元     </h3>
        	    <h3>总额 ：{$total}元 （滞纳金 $timeFee 元 + 工本费 $gb 元 ）  </h3>
        	     {$op}
        	    <p>
        	   		<div class="btn-group pull-right" e="text-aligin:center">
					    <button  class="btn btn-info pull-right  carOff" data-id="$eid" data-op="carOff"  >确认车辆离场</button>
					</div>
        	    </p>
        	   
        </div>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
EOT;
    }

    protected function script()
    {
        return <<<EOT
		
		$('.carOff').on('click', function() {
		    	var op = $(this).attr('data-op');
			    var id = $(this).attr('data-id');
        		var price =$("#finalprice_"+id).val();
    			var memo =$("#memo_"+id).val();
	    		$.post('/admin/op',{id:id,op:op,price:(price?price:0),memo:(memo?memo:'' )},function(data){
    					   //这里显示一下
        					toastr.success(data.message)
        					$('#grid-modal-state-'+id).modal('hide');
    					   $.pjax.reload({container: "#pjax-container", timeout: 5000});
    			});
		});

EOT;
    }
}