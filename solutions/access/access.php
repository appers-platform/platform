<?
namespace solutions;

class access extends solution {
	static public function init() {
		if(!parent::init())
			return false;
		\event::addCallback('beforeControllerRun', [__CLASS__, 'beforeControllerRun']);
		return true;
	}

	static public function beforeControllerRun() {
		$protect = self::getConfig('protect');
		if(!is_array($protect)) $protect = [$protect];
		$access_check = [];
		foreach($protect as $url => $access) {
			if(substr(\request::getUri(), 0, strlen($url)) == $url) {
				if(!is_array($access)) $access = [$access];
				if(!count($access)) continue;
				$ok = false;
				if(user::isAuthorized()) {
					foreach(self::getAccess(user::current()) as $user_access) {
						if(in_array($user_access, $access)) {
							$ok = true;
							break;
						}
					}
				}
				$access_check[strlen($url)] = $ok;
			}
		}
		if(count($access_check)) {
			if(!$access_check[max(array_keys($access_check))]) {
				if(!user::isAuthorized()) {
					user::setAfterAuthUrl(\request::getUri());
					\response::redirect(user::getUrl('login'));
				} else {
					\response::redirect(self::getUrl('denied'));
				}
			}
		}
	}

	static public function callbackUserDelete($user_id) {
		$access = \solutions\access\accessModel::modelWhere(['user_id' => $user_id]);
		$access->delete();
	}

	static protected function getUser(user\userModel $user = null) {
		if(!$user)
			$user = \solutions\user::current();
		if(!$user)
			throw new \Exception('Current user is undefined, and $user in arguments is empty.');
		return $user;
	}

	static protected function _getAccess(user\userModel $user) {
		$access = \solutions\access\accessModel::getWhere(['user_id' => $user->getPrimaryId()]);
		if(!$access)
			return [];
		if(!$access[0]->access_list)
			return [];
		return unserialize($access[0]->access_list);
	}

	static public function addUserAccess($access, user\userModel $user = null) {
		$user = self::getUser($user);
		$access_list = self::_getAccess($user);
		if(!in_array($access, $access_list)) {
			$access_list[] = $access;
		}
		self::setAccess($access_list, $user);
	}

	static public function removeUserAccess($access, user\userModel $user = null) {
		$user = self::getUser($user);
		$access_list = self::_getAccess($user);
		if(($key = array_search($access, $access_list)) !== false) {
			unset($access_list[$key]);
		}
		self::setAccess($access_list, $user);
	}

	static public function checkUserAccess($access, user\userModel $user = null) {
		$access_list = self::_getAccess(self::getUser($user));
		return in_array($access, $access_list);
	}

	static public function getAccess(user\userModel $user = null) {
		return self::_getAccess(self::getUser($user));
	}

	static public function setAccess(array $access_list, user\userModel $user = null) {
		$user = self::getUser($user);

		$access = \solutions\access\accessModel::modelWhere(['user_id' => $user->getPrimaryId()]);
		$access->user_id = $user->getPrimaryId();
		$access->access_list = serialize($access_list);
		$access->store();
	}
}
