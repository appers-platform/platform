<?
namespace solutions\user;

class registration_solutionController extends parentController {
	public function first() {
		if(\solutions\user::current()) {
			\response::redirectTop(\solutions\user::getAfterAuthUrl());
		}
	}
	public function post() {
		if(!$this->value_email = \request::get('email')) {
			$this->message = __('Enter email, please');
		} else {
			if(userModel::getByEmail($this->value_email)) {
				\log::debug('Email already exists');
				$this->message = __('Email already exists');
			} else if(!strpos($this->value_email, '@')) {
				$this->message = __('Email is incorrect');
				\log::debug('Email is incorrect');
			} else {
				\log::debug('Creating user...');
				$user = new userModel();
				$user->email = $this->value_email;
				$user = self::register($user);

				$user_auth = new userAuthModel();
				$user_auth->after_auth_url = \solutions\user::getAfterAuthUrl();
				$user_auth->user_id = $user->getId();
				$user_auth->store();

				$this->setView('registrationDone');
				if(\request::isFrame()) {
					\js::addCallback('top.$$.solutions.widget.setNoClosable');
				}
				return;
			}
		}
	}
}
