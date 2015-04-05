<?
namespace solutions;
use solutions\user\userModel;

class user extends solution {
	const ENABLING_REQUIRED = true;
	
	static protected $user = null;
	static protected $after_auth_url = null;

	static public function init() {
		\solutions\widget::init();
		parent::init();
		if($after_auth_url = \request::get('afterAuthUrl')) {
			self::$after_auth_url = $after_auth_url;
		} else {
			self::$after_auth_url = self::getConfig('afterAuthUrl');
		}
	}

	static public function current() {
		if(self::$user === null) {
			if($user_id = (int) \session::get('id')) {
				self::$user = new userModel($user_id);
			} else if($user_id = (int) \helper::decode(\cookie::get('id'), static::getSecret().'_cookie')) {
				self::$user = new userModel($user_id);
			}
		}

		if(self::$user) {
			self::setCurrent(self::$user);
		} else {
			self::$user = false;
		}

		return self::$user;
	}

	static public function getCurrent() {
		return self::current();
	}

	static public function isAuthorized() {
		return (self::getCurrent() != false);
	}

	static public function getIdByEmail($email) {
		$model = new userModel();
		$model->email = $email;
		$model->find();
		return (int) $model->getId();
	}

	static public function isUserExists($id) {
		$model = new userModel();
		$model->id = $id;
		$model->find();
		return (bool) $model->getId();
	}

	public function getCurrentId() {
		if(self::isAuthorized()) {
			return self::current()->getPrimaryId();
		}

		return false;
	}

	static public function setCurrent($user) {
		if($user instanceof userModel) {
			self::$user = $user;
		} else if(is_numeric($user)) {
			self::$user = new userModel($user);
		} else if(is_null($user)) {
			self::$user = null;
			\session::delete('id');
			\cookie::delete('id');
			return;
		} else {
			throw new \Exception('Unknown type');
		}

		if(\session::get('id') != self::$user->getPrimaryId()) {
			\session::set('id', self::$user->getPrimaryId());
		}

		$cookie_id = \helper::encode((int) self::$user->getPrimaryId(), static::getSecret().'_cookie');
		if(\cookie::get( 'id' ) != $cookie_id) {
			\cookie::set( 'id', $cookie_id );
		}
	}

	static public function setAfterAuthUrl($url) {
		self::$after_auth_url = $url;
	}

	static public function getAfterAuthUrl() {
		return self::$after_auth_url;
	}
}
