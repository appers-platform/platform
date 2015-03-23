<?
namespace solutions\user;

class recover_solutionController extends parentController {
	public function get() {
		if($recover = \request::get('recover')) {
			$this->setView('message');
			$code = \helper::decode($recover, (string) self::getConfig('secret'));
			if(!$code) {
				$this->message = __('Recover code is incorrect');
				return;
			}

			list($email, $password) = explode('|', $code);
			if(!$email || !$password) {
				$this->message = __('Recover code is incorrect');
				return;
			}

			if(!($user = userModel::getByEmail($email))) {
				$this->message = __('Recover code is incorrect');
				return;
			}

			if($user->password != $password) {
				$this->message = __('Recover code is incorrect');
				return;
			}

			self::sendNewPassword($user);
			$this->message = __('New password has been sent to your mail');
		}
	}

	public function post() {
		if(!$this->value_email = \request::get('email')) {
			$this->message = __('Enter email, please');
		} else if(!$user = userModel::getByEmail($this->value_email)) {
			$this->message = __('Email or password is incorrect');
		} else {
			self::sendRecoverMail($user);
			$this->message = __('Instructions has been sent to your mail.');
			$this->setView('message');
		}
	}

	static public function sendNewPassword(userModel $user) {
		$plain_password = \helper::generatePassword();

		$mail_content = __('Hi!
Your new password: %s

', $plain_password, $user->email);

		\solutions\mail::send(
			$user->email,
			__('Recovering password'),
			nl2br($mail_content)
		);

		$user->password = md5($plain_password);
		$user->store();

		return $user;
	}

	static public function sendRecoverMail(userModel $user) {
		$recover = \helper::encode($user->email.'|'.$user->password, (string) self::getConfig('secret'));
		$url = 'http://'.\request::getHost().self::getUrl('recover').'?recover='.urlencode($recover);
		$mail_content = __('You, or someone else try to recover password.');
		$mail_content .= "\n";
		$mail_content .= __('<link>Click</link> for complete recovering.');
		$mail_content = \solutions\placeholer::link($mail_content, $url);

		\solutions\mail::send(
			$user->email,
			__('Recovering password'),
			nl2br($mail_content)
		);

		return true;
	}
}
