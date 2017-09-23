<?php
namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\PlainInput;

class AutoCompleteAjax extends Field
{
	
	use PlainInput;
	
	protected $view = 'admin.autocomplete';

	protected static $css = [
		'/packages/admin/jQuery-Autocomplete/jquery.autocomplete.css',
		//'/ueditor/themes/default/css/ueditor.min.css',
	];

	protected static $js = [
		'/packages/admin/jQuery-Autocomplete/jquery.autocomplete.min.js',
	];

	public function render()
	{

		$this->prepend('<i class="fa fa-pencil"></i>')
		->defaultAttribute('type', 'text')
		->defaultAttribute('id', $this->id)
		->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
		->defaultAttribute('value', old($this->column, $this->value()))
		->defaultAttribute('class', 'form-control dropdown-input '.$this->getElementClassString())
		->defaultAttribute('placeholder', $this->getPlaceholder());
		

		  $this->script = <<<EOT
		  	param = {};
		  	$('#{$this->id}').autocomplete({
			    serviceUrl: '/admin/api/drivers',
		  		params:param,
		  		deferRequestBy:500,
		  		minChars:2,
		  		triggerSelectOnValidInput:true,
		  		onSearchStart:function(suggest){
		  			//console.log(suggest);
		  			var eid = $("select[name=exhibition_id]").val();
		  			if(eid){
				  		param.eid = eid;
					}
		  			if( !suggest ){
		  			 return false;
					}
		  			/* var res = /^[\u4e00-\u9fa5]$/.test(suggest.query.substr(0,1) ); 
		  			console.log(res);
		  			 if(res){
		  			 return true;
					}  */
		  			return true;
				},
		  		preventBadQueries:true,
			    onSelect: function (suggestion) {
			       // alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
		  			if(suggestion){
						console.log(suggestion);
		  				$("#driver").val(suggestion.name);
		  				$("#contact").val(suggestion.mobile);
		  				$("#exhibition_no").val(suggestion.exhibition_no);
		  			    $("select[name=unload_area]").val(suggestion.unload_area).trigger("change");
		  			    $("select[name=exhibition_pos]").val(suggestion.exhibition_pos).trigger("change")
					}
			    }
			});

EOT;
		return parent::render()->with([
            'prepend' => $this->prepend,
            'append'  => $this->append,
        ]);

	}
	
}