<?
$text_enter_with = __('Enter with');
$html_social = [];

foreach(['Mail.ru' => 'mailru'] as $name => $controller) {
	$url = $this->getUrl('oauth_'.$controller).'?afterAuthUrl='.urlencode(\solutions\user::getAfterAuthUrl());
	$html_social[] = "
		<a target='_top' href='{$url}' class='s_auth'>
			<img src='/solutions-public/user/images/icons/37/{$controller}.png' />
			{$text_enter_with} {$name}
		</a>
	";
}

$html_social[] = '<div class="orBlock"><hr><div>'.__('OR').'</div></div>';

$html_add = '<div class="orBlock"><hr><div>'.__('OR').'</div></div>';
$html_add .= '<a target="_self" href="'.$this->getUrl('login', [
	'_frame' => \request::getInt('_frame'),
	'afterAuthUrl' => \solutions\user::getAfterAuthUrl()
]).'" class="a_link">'.__('Sign in').'</a>';

$form = \solutions\form::create(
	[
		new \solutions\form\html(implode($html_social)),
		[
			'name' => 'email',
			'title' => __('Email'),
			'value' => $value_email
		],
		[ 'name' => '_frame', 'type' => 'hidden', 'value' => \request::getInt('_frame') ],
		[ 'name' => 'afterAuthUrl', 'type' => 'hidden', 'value' => \request::get('afterAuthUrl') ]
	],
	__('Registration'),
	'?',
	'POST',
	[
		new \solutions\form\html($html_add)
	]
)->setSendButtonName(__('Sign up'));

if(isset($message))
	$form->setMessage($message);

if(isset($this->getConfig('css')['formClass']))
	$form->setClass($this->getConfig('css', false)['formClass']);

print '<div class="__solutions_user">'.$form.'</div>';
