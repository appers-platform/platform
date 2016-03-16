<?
return [
	'css' => ['formClass' => ''],

	'oauth_mail_ru' => [
		'id'			=> 0,
		'private_key' 	=> 'your_private_key',
		'secret_key' 	=> 'your_secret_key'
	],

	'mailSolution'		=> [ 'enabled' => true ],

	'routes' => [
		'/user/registration'	=> 'registration',
		'/user/login'			=> 'login',
		'/user/exit'			=> 'exit',
		'/user/confirm'			=> 'confirm',
		'/user/oauth/mailru'	=> 'oauth_mailru',
		'/user/recover'			=> 'recover',
		'/user/password'		=> 'password',
		'/user/email'			=> 'email',
		'/user/settings'		=> 'settings',
		'/user/name'			=> 'name'
	],

	'afterAuthUrl' => '/',

	'registration_email_title'	=> __('Registration')
];
