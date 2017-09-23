<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class Synchronize extends AbstractTool
{
	private $type = 1;
	
	public function __construct($type){
		$this->type = $type;
	}
	
    protected function script()
    {
        $url = Request::fullUrlWithQuery([]);

        return <<<EOT

$('.synchronize').click(function () {
   //alert('test');
	$.post("/admin/api/synch",{type:"{$this->type}"},function(data){
			 var url = "$url";
    	     $.pjax({container:'#pjax-container', url: url });
    });
});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        /* $options = [
            'all'   => 'All',
            'm'     => 'Male',
            'f'     => 'Female',
        ]; */

        return view('admin.tools.gender');
    }
}