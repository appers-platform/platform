<?
namespace solutions\comment;

if (!\request::isPost())
	return $this->returnJson(['result' => false]);

if(!\solutions\user::getCurrent()) {
	// TODO: Context sign up or sign in
	return $this->returnJson(['result' => false, 'message' => __('Login, please')]);
}

if(!($model_name = \request::get('solution_comment_model')))
	return $this->returnJson(['result' => false, 'message' => 'error 1']);

if(md5($model_name.self::getConfig('secret')) != \request::get('sign'))
	return $this->returnJson(['result' => false, 'message' => 'error 2']);

if(!$comment_id = \request::getInt('comment_id'))
	return $this->returnJson(['result' => false, 'message' => 'error 3']);

$comment = new $model_name($comment_id);
if(!$comment->getId())
	return $this->returnJson(['result' => false, 'message' => 'error 4']);

if($comment->user_id != \solutions\user::getCurrent()->getId())
	return $this->returnJson(['result' => false, 'message' => 'error 5']);

function deleteSubComments($parent_id, $model_name) {
	$find = new $model_name();
	$find->parent_id = $parent_id;
	foreach($find->findAll() as $sub_comment) {
		deleteSubComments($sub_comment->getId(), $model_name);
		$sub_comment->delete();
	}
}

deleteSubComments($comment_id, $model_name);
$comment->delete();

$this->returnJson(['result' => true, 'message' => __('Comment has been deleted')]);
