<?
return [
	'css' => ['formClass' => 'col-xs-6 col-md-4'],

	'secret' => '(74997s(&(&&^%$%^&',

	'oauth_mail_ru' => [
		'id'			=> 0,
		'private_key' 	=> 'your_private_key',
		'secret_key' 	=> 'your_secret_key'
	],

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

	'afterAuthUrl' => '/'
];
