<?php
namespace App\Admin\Extensions;

use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\PlainInput;

class AutoComplete extends Field
{
	
	use PlainInput;
	
	protected $view = 'admin.autocomplete';

	protected static $css = [
	    '/packages/admin/awesomplete/awesomplete.css',
		//'/ueditor/themes/default/css/ueditor.min.css',
	];

	protected static $js = [
		'/packages/admin/awesomplete/awesomplete.js',
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
				var input = document.getElementById("{$this->id}");
		  		new Awesomplete(input , {
				list: ["aol.com", "att.net", "comcast.net", "facebook.com", "gmail.com", "gmx.com", "googlemail.com", "google.com", "hotmail.com", "hotmail.co.uk", "mac.com", "me.com", "mail.com", "msn.com", "live.com", "sbcglobal.net", "verizon.net", "yahoo.com", "yahoo.co.uk"],
				data: function (text, input) {
					return input.slice(0, input.indexOf("@")) + "@" + text;
				},
				filter: Awesomplete.FILTER_STARTSWITH
			});

EOT;
		return parent::render()->with([
            'prepend' => $this->prepend,
            'append'  => $this->append,
        ]);

	}
	
}