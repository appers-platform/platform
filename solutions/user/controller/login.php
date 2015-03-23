<?
namespace solutions\user;

class login_solutionController extends parentController {
	

	public function first() {
		if(\solutions\user::current()) {
			\response::redirectTop(\solutions\user::getAfterAuthUrl());
		}
	}


	public function post() {
		if(!$this->value_email = \request::get('email')) {
			$this->message = __('Enter email, please');
		} else if(!$password = \request::get('password')) {
			$this->message = __('Enter password, please');
		} else if(!$user = userModel::getByEmail($this->value_email)) {
			$this->message = __('Email or password is incorrect');
		} else if($user->password != md5($password)){
			$this->message = __('Email or password is incorrect');
			$this->show_recover_link = true;
		} else {
			\solutions\user::setCurrent($user);
			\response::redirectTop(\solutions\user::getAfterAuthUrl());
		}
	}

}
