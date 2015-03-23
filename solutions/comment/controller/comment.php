<?
namespace solutions\comment;

$model_name = $this->model_name;

if(!$model_name)
	throw new \Exception('model_name is not set');

$search = new $model_name();
$search->target_element_id = (int) $this->target_element_id;
$search->parent_id = 0;
$this->comments = $search->findAll(['last_time', 'desc']);
$this->comments_count = count($this->comments);

function loadSubComments($parent, $target_element_id, $model_name, &$sub_comments) {
	$search = new $model_name();
	$search->target_element_id = (int) $target_element_id;
	$search->parent_id = $parent->getId();
	$sub_comments[$parent->getId()] = $search->findAll(['last_time', 'asc']);

	foreach($sub_comments[$parent->getId()] as $sub_comment) {
		loadSubComments($sub_comment, $target_element_id, $model_name, $sub_comments);
	}
}

$sub_comments = [];
foreach($this->comments as $comment)
	loadSubComments($comment, $this->target_element_id, $model_name, $sub_comments);
$this->sub_comments = $sub_comments;

\js::setVar('solution_comment_routes', [
	'add'		=> self::getUrl('add'),
	'delete'	=> self::getUrl('delete'),
	'complaint'	=> self::getUrl('complaint'),
]);
\js::setVar('solution_comment_model', [
	'name'	=> $this->model_name,
	'sign'	=> md5($this->model_name.self::getConfig('secret'))
]);
\js::setVar('solution_comment_text', [
	'ok' => __('Ok'),
	'confirm_delete'	=> __('Are you really want to remove this comment?'),
	'confirm_spam'		=> __('Are you really want mark this comment as spam?'),
	'confirm_complaint'	=> __('Are you sure you want to send a complaint to this comment?'),
]);
