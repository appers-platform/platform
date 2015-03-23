<?
namespace solutions\user;

class userModel extends \model {
	const GENDER_UNDEFINED	= 0;
	const GENDER_FEMALE		= 1;
	const GENDER_MALE 		= 2;

	const OAUTH_NONE = 0;
	const OAUTH_MAILRU = 1;

	/*
	public $social_id;
	public $social_auth_id;
	public $email;
	public $photo_url;
	public $birthday; // int
	public $url;
	public $is_male;
	public $name;
	public $is_confirmed;
	*/

	static public function getByEmail($email) {
		$result = self::getWhere(['email' => $email]);
		if($result)
			return $result[0];
		return false;
	}

	public function __get_name($name) {
		if(!$name && $p = strpos($this->email, '@')) {
			$name = substr($this->email, 0, $p);
			$name = ucwords(str_replace('.', ' ', $name));
		}

		return $name;
	}

	public function delete() {
		$id = $this->getId();
		parent::delete();
		if($id) {
			\event::fire('delete', [$id]);
		}
	}
}
