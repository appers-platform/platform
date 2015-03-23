<?
use ohmy\Auth2;

if(\solutions\user::isAuthorized()) {
	\response::redirect(self::getConfig('afterAuthUrl'));
}

// @TOOD: url of this page
$redirect = \helper::fullUrl(self::getUrl().'?afterAuthUrl='.urlencode(\solutions\user::getAfterAuthUrl()));

# initialize 3-legged oauth
$auth = Auth2::legs(3)
	->set('id', $this->getConfig('oauth_mail_ru')['id'])
	->set('secret', $this->getConfig('oauth_mail_ru')['secret_key'])
	->set('redirect', $redirect)

	# oauth flow
	->authorize('https://connect.mail.ru/oauth/authorize')
	->access('https://connect.mail.ru/oauth/token')

	# save access token
	->finally(function($data) use(&$access_token) {
		$access_token = $data['access_token'];
	});

// get user data
$params = [
	'method' => 'users.getInfo',
	'session_key' => $access_token,
	'app_id' => '722752',
	'format' => 'json',
	'secure' => 1
];

// sign query
ksort($params);
$params_str = '';
foreach($params as $key => $value) {
	$params_str .= "$key=$value";
}
$params['sig'] = md5($params_str.$this->getConfig('oauth_mail_ru')['secret_key']);

// execute: get user data
$auth->GET('http://www.appsmail.ru/platform/api', $params)->then(function($response) {
	$data = $response->json();
	if(!count($data) || !$data[0]) {
		parentController::oauth(new userModel());
		return;
	}

	$prepared = new userModel();
	$data = $data[0];

	$prepared->email = $data['email'];
	$prepared->social_id = $data['uid'];
	$prepared->social_auth_id = userModel::OAUTH_MAILRU;
	$prepared->is_confirmed = true;

	$prepared->birthday = strtotime($data['birthday']);
	$prepared->photo_url = $data['pic_big'];
	$prepared->is_male = $data['sex'] ? userModel::GENDER_FEMALE : userModel::GENDER_MALE;
	$prepared->url = $data['link'];
	$prepared->name = $data['nick'];

	parentController::oauth($prepared);
});




