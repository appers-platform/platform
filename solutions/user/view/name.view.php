<?
$form = \solutions\form::create(
	[
		[
			'name' => 'name',
			'type' => 'text',
			'title' => __('Name'),
			'value' => $name
		],
	],
	__('Change name'),
	'?',
	'POST'
)->setSendButtonName(__('Save'))->setCancelButton(__('Cancel'), $this->getUrl('settings'));

if(isset($message))
	$form->setMessage($message);

if(isset($this->getConfig('css')['formClass']))
	$form->setClass($this->getConfig('css', false)['formClass']);

print $form;
