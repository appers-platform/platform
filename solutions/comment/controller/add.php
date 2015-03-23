<?
namespace solutions\comment;

use solutions\comment;

if(!\solutions\user::getCurrent()) {
	return $this->returnJson([
		'result' => false,
		//'message' => __('Login, please')],
		'callback' => '$$.solutions.user.showRegisterForm',
		'callback_args' => [
			self::getUrl().'?back_url='.urlencode(\request::getReferer()).'&'.http_build_query(\request::getAll(true))
		]
	]);
}

if(!\request::get('comment'))
	return $this->returnJson(['result' => false, 'message' => __('Enter your comment, please')]);

if(!($model_name = \request::get('solution_comment_model')))
	return $this->returnJson(['result' => false, 'message' => 'error 1']);

if(md5($model_name.self::getConfig('secret')) != \request::get('sign'))
	return $this->returnJson(['result' => false, 'message' => 'error 2']);

if($parent_id = \request::getInt('parent_id')) {
	$parent = new $model_name($parent_id);
	if($parent->user_id == \solutions\user::getCurrent()->getId())
		return $this->returnJson(['result' => false, 'message' => 'error 3']);

	comment::up($parent);
}

$comment = new $model_name();
$comment->text = \request::get('comment');
$comment->user_id = \solutions\user::getCurrent()->getId();
$comment->target_element_id = \request::getInt('target_element_id');
$comment->parent_id = $parent_id;
$comment->last_time = time();
$comment->time = time();
$comment->insert();

if($back_url = \request::get('back_url')) {
	$back_url .= ( strpos($back_url, '?') === false ? '?' : '&' ).'$$_solutions_widget_alert='.urlencode(__('Your comment has been added'));
	\response::redirect($back_url);
}

return $this->returnJson([
	'result' => true,
	'message' => __('Your comment has been added'),
	'html' => \renderer::renderPartial('comment', [ 'comment' => $comment])
]);


