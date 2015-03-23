<?
namespace solutions\voting;
use solutions\voting;

if(!($vote = \request::get('vote')))
	return $this->returnJson(['result' => false, 'message' => 'error 6']);

if(!$this->getCalledData('enabled', true))
	return $this->returnJson(['result' => false, 'message' => 'error 7']);

if(!\solutions\user::getCurrent()){
	return $this->returnJson([
		'result' => false,
		//'message' => __('Login, please')],
		'callback' => '$$.solutions.user.showRegisterForm',
		'callback_args' => [
			self::getUrl().'?back_url='.urlencode(\request::getReferer()).'&'.http_build_query(\request::getAll(true))
		]
	]);
	//return $this->returnJson(['result' => false, 'message' => __('Login first, please.')]);
}

$vote = $vote == '+' ? '+' : '-';
// TODO: @
$current_user_id = \solutions\user::getCurrent()->getId();

$model_name = $this->getCalledData('model_name', true);
$model = new $model_name;
$model->target_element_id = $this->getCalledData('target_element_id', true);
$model->dependency_entity = $this->getCalledData('dependency_entity', true);
$model->find();

if(voting::_checkAlreadyVoted($vote, $model_name, $model->target_element_id, $model->dependency_entity)) {
	if($back_url = \request::get('back_url')) {
		$back_url .= ( strpos($back_url, '?') === false ? '?' : '&' ).'$$_solutions_widget_alert='.urlencode(__('You already voted'));
		\response::redirect($back_url);
	}
	return $this->returnJson(['result' => false, 'message' => __('You already voted')]);
}

if($vote == '+') {
	$voted_minus = explode(',', $model->voted_users_miunus);
	if(in_array($current_user_id, $voted_minus)) {
		$model->voted_users_miunus = implode(',', array_diff([$current_user_id], $voted_minus));
	} else {
		$voted_users_plus = array_merge([$current_user_id], explode(',', $model->voted_users_plus));
		if (count($voted_users_plus) > 1000) $voted_users_plus = array_slice($voted_users_plus, 0, 1000);
		$model->voted_users_plus = implode(',', $voted_users_plus);
	}
	$model->rate = 1 + (int) $model->rate;
} else {
	$voted_plus = explode(',', $model->voted_users_plus);
	if(in_array($current_user_id, $voted_plus)) {
		$model->voted_users_plus = implode(',', array_diff([$current_user_id], $voted_plus));
	} else {
		$voted_users_miunus = array_merge([$current_user_id], explode(',', $model->voted_users_miunus));
		if(count($voted_users_miunus) > 1000) $voted_users_miunus = array_slice($voted_users_miunus, 0, 1000);
		$model->voted_users_miunus = implode(',', $voted_users_miunus);
	}
	$model->rate = ((int) $model->rate) - 1;
}

$model->store();

if($callback = $this->getCalledData('callback'))
	call_user_func($callback, $this->getCalledData('callback_data'));

if($back_url = \request::get('back_url')) {
	\response::redirect($back_url);
}

$this->returnJson(['result' => true, 'rate' => $model->rate]);