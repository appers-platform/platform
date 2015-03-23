<?
namespace solutions\user;
use request;
use \solutions\user;
use \solutions\mail;
use \solutions\placeholer;

class password_solutionController extends parentController {
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

			list($email, $password, $new_password) = explode('|', $code);
			if(!$email || !$password || !$new_password) {
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

			$user->password = $new_password;
			$user->save();
			$this->message = __('Password has been successfully changed');
		}
	}

	public function post() {
		if(!user::isAuthorized())
			return;

		if(!($password = request::get('password'))) {
			$this->message = __('Please, enter current password');
		} else if(!($new_password = request::get('new_password'))) {
			$this->message = __('Please, enter new password');
		} else if(!request::get('repeat_new_password')) {
			$this->message = __('Please, repeat new password');
		} else if(user::getCurrent()->password != md5($password)) {
			$this->message = __('Current password is incorrect');
		} else if(request::get('new_password') != request::get('repeat_new_password')) {
			$this->message = __('Repeat of new password is incorrect');
		} else {
			self::sendContinueMail(user::getCurrent(), $new_password);
			$this->message = __('Check your mail for continue password change');
			$this->setView('message');
		}
	}

	static public function sendContinueMail(userModel $user, $new_password) {
		$data = $user->email.'|'.$user->password.'|'.md5($new_password);
		$update = \helper::encode($data, (string) self::getConfig('secret'));
		$url = 'http://'.\request::getHost().self::getUrl('password').'?update='.urlencode($update);
		$mail_content = __('<link>Click</link> for confirm password change.');
		$mail_content = placeholer::link($mail_content, $url);

		mail::send(
			$user->email,
			__('Change password'),
			nl2br($mail_content)
		);

		return true;
	}
}