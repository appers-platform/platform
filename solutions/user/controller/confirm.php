<?
namespace solutions\user;
use \log;

$confirm = \helper::decode(
	\request::get('confirm'),
	(string) \solutions\user::getSecret()
);
$model = userModel::getByEmail($confirm);

if($model->getPrimaryId()) {
	log::debug('confirm:src:'.\request::get('confirm'));
	log::debug('confirm:dec:'.$confirm);
	log::debug('confirm:pk:'.$model->getPrimaryId());

	$url = \solutions\user::getAfterAuthUrl();
	
	if(!$model->is_confirmed) {
		$model->is_confirmed = 1;
		log::debug('confirm:pk2:'.$model->getPrimaryId());
		$model->save();
		log::debug('confirm:pk3:'.$model->getPrimaryId());

		$after_auth_model = new userAuthModel();
		$after_auth_model->user_id = $model->getId();
		if($after_auth_model->load()) {
			$url = $after_auth_model->after_auth_url;
			$after_auth_model->delete();
		}
	}
	
	log::debug('confirm:pk4:'.$model->getPrimaryId());
	\solutions\user::setCurrent($model);
	\response::redirect($url);
} else {
	$this->text = __('Code is invalid');
}
