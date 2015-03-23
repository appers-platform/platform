<?
namespace solutions\user;
use request;
use \solutions\user;

class name_solutionController extends parentController {
	public function first() {
		if(!user::getCurrent())
			\response::redirect(self::getUrl('login'));
	}

	public function post() {
		if(!user::isAuthorized())
			return;

		if(!($name = request::get('name'))) {
			$this->message = __('Please, enter new name');
		} else {
			user::getCurrent()->name = $name;
			user::getCurrent()->save();
			$this->message = __('Your name has been changed');
		}
	}

	public function last() {
		$this->name = user::getCurrent()->name;
	}
}