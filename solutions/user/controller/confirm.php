<?
namespace solutions\user;
use \log;

$confirm = \helper::decode(
	\request::get('confirm'),
	(string) \solutions\user::getSecret()
);

if($model = userModel::getByEmail($confirm)) {
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
	log::debug('model-A:'.print_r($model, true));
	\solutions\user::setCurrent($model);
	\response::redirect($url);
} else {
	$this->text = __('Code is invalid');
}
