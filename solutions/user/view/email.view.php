<?
$form = \solutions\form::create(
	[
		[
			'name' => 'new_email',
			'type' => 'text',
			'title' => __('New email'),
		],
	],
	__('Change email'),
	'?',
	'POST'
)->setSendButtonName(__('Save'))->setCancelButton(__('Cancel'), $this->getUrl('settings'));

if(isset($message))
	$form->setMessage($message);

if(isset($this->getConfig('css')['formClass']))
	$form->setClass($this->getConfig('css', false)['formClass']);

print $form;
