<?
namespace solutions;

class comment extends solution {
	const ENABLING_REQUIRED = true;

	static protected function getModelName($dependency_entity) {
		return '\\solutions\\comment\\'.$dependency_entity.'_CommentModel';
	}

	static public function show( $dependency_entity, $target_element_id = null ) {
		$dependency_entity = 'm'.md5($dependency_entity);
		widget::init();

		$controller = self::controller('comment');
		$controller->dependency_entity = $dependency_entity;
		$controller->target_element_id = $target_element_id;
		$controller->model_name = self::getModelName($dependency_entity);
		$controller->sign = md5($controller->model_name.self::getConfig('secret'));
		print $controller;
		return (int) $controller->comments_count;
	}

	static public function up($comment) {
		$comment->last_time = time();
		$comment->save();
		if($parent_id = $comment->parent_id) {
			$model_name = get_class($comment);
			$parent = new $model_name($parent_id);
			do {
				$parent->last_time = time();
				$parent->save();
				if(!$parent->parent_id)
					break;
				$parent = new $model_name($parent->parent_id);
			} while( $parent->getId() );
		}
	}

	static public function rateCallback($data) {
		list($model_name, $model_id) = $data;
		if(!$model_name || !$model_id) return;
		self::up(new $model_name($model_id));
	}
}
