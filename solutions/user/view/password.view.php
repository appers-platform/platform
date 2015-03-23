<?
$form = \solutions\form::create(
	[
		[
			'name' => 'password',
			'type' => 'password',
			'title' => __('Current password'),
		],
		[
			'name' => 'new_password',
			'type' => 'password',
			'title' => __('New password'),
		],
		[
			'name' => 'repeat_new_password',
			'type' => 'password',
			'title' => __('Repeat new password'),
		],
	],
	__('Change password'),
	'?',
	'POST'
)->setSendButtonName(__('Save'))->setCancelButton(__('Cancel'), $this->getUrl('settings'));

if(isset($message))
	$form->setMessage($message);

if(isset($this->getConfig('css')['formClass']))
	$form->setClass($this->getConfig('css', false)['formClass']);

print $form;
