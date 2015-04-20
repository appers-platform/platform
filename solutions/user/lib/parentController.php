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

	public function register(userModel $user) {
		$plain_password = \helper::generatePassword();

		$confirmation_url = false;
		if(!$user->is_confirmed) {
			$confirm = \helper::encode($user->email, (string) \solutions\user::getSecret());
			$confirmation_url = 'http://'.\request::getHost().self::getUrl('confirm').'?confirm='.urlencode($confirm);
		}

		$this->sendEmail($user->email, $plain_password, $confirmation_url);

		if(userModel::getByEmail($user->email)) {
			throw new \Exception('How it possible?!');
		}

		$user->password = md5($plain_password);
		$user->insert();

		return $user;
	}

	public function sendEmail($email, $password, $confirmation_url) {
		$mail_content = $this->renderFile(
			$this->getView($confirmation_url ? 'registration_email_with_confirm' : 'registration_email'),
			[
				'email'				=> $email,
				'password'			=> $password,
				'confirmation_url'	=> $confirmation_url
			]
		);

		\solutions\mail::send(
			$email,
			static::getConfig('registration_email_title'),
			nl2br($mail_content)
		);
	}

	static public function signIn(userModel $user) {
		\solutions\user::setCurrent($user);
	}
}
