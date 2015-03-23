<?
namespace solutions\user;
use request;
use \solutions\user;
use \solutions\mail;
use \solutions\placeholer;

class email_solutionController extends parentController {
	public function first() {
		if(!user::getCurrent())
			\response::redirect(self::getUrl('login'));
	}

	public function get() {
		if($update = \request::get('update')) {
			$this->setView('message');
			$code = \helper::decode($update, (string) self::getConfig('secret'));

			if(!$code) {
				$this->message = __('Code is incorrect');
				return;
			}

			list($email, $password, $new_email) = explode('|', $code);
			if(!$email || !$password || !$new_email) {
				$this->message = __('Code is incorrect');
				return;
			}

			if(!($user = userModel::getByEmail($email))) {
				$this->message = __('Code is incorrect');
				return;
			}

			if(((string)$user->password) != ((string)$password)) {
				$this->message = __('Code is incorrect');
				return;
			}

			$user->email = $new_email;
			$user->save();
			$this->message = __('Email has been successfully changed');
		}
	}

	public function post() {
		if(!user::isAuthorized())
			return;

		if(!($new_email = request::get('new_email'))) {
			$this->message = __('Please, enter new email');
		} else {
			self::sendContinueMail(user::getCurrent(), $new_email);
			$this->message = __('Check your old mail for continue email change');
			$this->setView('message');
		}
	}

	static public function sendContinueMail(userModel $user, $new_email) {
		$data = $user->email.'|'.$user->password.'|'.$new_email;
		$update = \helper::encode($data, (string) self::getConfig('secret'));
		$url = 'http://'.\request::getHost().self::getUrl('email').'?update='.urlencode($update);
		$mail_content = __('<link>Click</link> for confirm email change.');
		$mail_content = placeholer::link($mail_content, $url);

		mail::send(
			$user->email,
			__('Change email'),
			nl2br($mail_content)
		);

		return true;
	}
}