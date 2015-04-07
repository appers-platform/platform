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
		log::debug('confirm:pk1:'.$model->getPrimaryId());

		$after_auth_model = new userAuthModel();
		log::debug('confirm:pk2:'.$model->getPrimaryId());
		$after_auth_model->user_id = $model->getId();
		log::debug('confirm:pk3:'.$model->getPrimaryId());
		if($after_auth_model->load()) {
			log::debug('confirm:pk4:'.$model->getPrimaryId());
			$url = $after_auth_model->after_auth_url;
			log::debug('confirm:pk5:'.$model->getPrimaryId());
			$after_auth_model->delete();
			log::debug('confirm:pk6:'.$model->getPrimaryId());
		}
	}
	
	log::debug('confirm:pk7:'.$model->getPrimaryId());
	\solutions\user::setCurrent($model);
	\response::redirect($url);
} else {
	$this->text = __('Code is invalid');
}
