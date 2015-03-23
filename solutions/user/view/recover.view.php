<?
$form = \solutions\form::create(
	[
		[
			'name' => 'email',
			'title' => __('Email'),
			'value' => ''
		]
	],
	__('Recovering password'),
	'?',
	'POST'
)->setSendButtonName(__('Recover password'))->setCancelButton('Cancel', $this->getUrl('settings'));

if(isset($message))
	$form->setMessage($message);

if(isset($this->getConfig('css')['formClass']))
	$form->setClass($this->getConfig('css', false)['formClass']);

print $form;
