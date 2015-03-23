<?
namespace solutions;
use \solutions\user;

class voting extends solution {
	/**
	 * @param string|int $dependency_entity
	 * @param int $target_element_id
	 * @param bool $disabled
	 * @param array|string $callback
	 * @param mixed $callback_data
	 * @throws \Exception
	 */
	static public function show( $dependency_entity, $target_element_id = null,  $disabled = false, $callback = null, $callback_data = null) {
		$dependency_entity = 'm'.md5($dependency_entity);

		if($callback) {
			if(
				(!is_array($callback) && !is_string($callback)) ||
				!is_callable($callback)
			)
				throw new \Exception('$callback is not callable');
		}

		widget::init();

		self::callScripts($script_data = [
			'dependency_entity'		=> $dependency_entity,
			'target_element_id'		=> (int) $target_element_id,
			'callback'				=> $callback,
			'callback_data'			=> $callback_data,
			'model_name'			=> '\\solutions\\voting\\'.$dependency_entity.'_VotingModel',
			'vote_url'				=> self::getUrl('rate'),
			'enabled'				=> !$disabled
		]);

		print self::controller('vote', $script_data);
	}

	static public function _checkAlreadyVoted($vote, $model_name, $target_element_id, $dependency_entity) {
		if(!user::getCurrent())
			return false;

		$model = new $model_name;
		$model->target_element_id = $target_element_id;
		$model->dependency_entity = $dependency_entity;
		$model->find();

		if(!$model->getId())
			return false;

		$current_user_id = user::getCurrent()->getId();

		if($vote == '+' && in_array($current_user_id, explode(',', $model->voted_users_plus)))
			return true;

		if($vote == '-' && in_array($current_user_id, explode(',', $model->voted_users_miunus)))
			return true;

		return false;
	}
}
