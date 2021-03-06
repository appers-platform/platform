<?
namespace solutions\user;
use \log;

$confirm = \helper::decode(
	\request::get('confirm'),
	(string) \solutions\user::getSecret()
);
$model = userModel::getByEmail($confirm);

if($model->getPrimaryId()) {
	$url = \solutions\user::getAfterAuthUrl();
	
	if(!$model->is_confirmed) {
		$model->is_confirmed = 1;
		$model->save();

		$after_auth_model = new userAuthModel();
		$after_auth_model->user_id = $model->getId();
		if($after_auth_model->load()) {
			$url = $after_auth_model->after_auth_url;
			$after_auth_model->delete();
		}
	}
	
	\solutions\user::setCurrent($model);
	\response::redirect($url);
} else {
	$this->text = __('Code is invalid');
}
