<?
namespace solutions\user;

class parentController extends \solutionController {
	static public function oauth(userModel $data) {
		if(!$data->email)
			\response::redirect(self::getUrl('login'));

		if($user = userModel::getByEmail($data->email)) {
			\solutions\user::setCurrent($user);
		} else {
			$data->is_confirmed = 1;
			$user = self::register($data);
			\solutions\user::setCurrent($user);
		}

		\response::redirect(\solutions\user::getAfterAuthUrl());
	}

	static public function register(userModel $user) {
		$plain_password = \helper::generatePassword();

		$mail_content = __('Hi!

Thank you for registration.
Your email: %s
Your password: %s

', $user->email, $plain_password);

		if(!$user->is_confirmed) {
			$confirm = \helper::encode($user->email, (string) \solutions\user::getSecret());
			$url = 'http://'.\request::getHost().self::getUrl('confirm').'?confirm='.urlencode($confirm);

			$mail_content .= __('<link>Click</link> for complete your registration.');
			$mail_content = \solutions\placeholer::link($mail_content, $url);
		}

		\solutions\mail::send(
			$user->email,
			__('Registration'),
			nl2br($mail_content)
		);

		if(userModel::getByEmail($user->email)) {
			throw new \Exception('How it possible?!');
		}

		$user->password = md5($plain_password);
		$user->insert();

		return $user;
	}

	static public function signIn(userModel $user) {
		\solutions\user::setCurrent($user);
	}
}
