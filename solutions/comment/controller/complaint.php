<?
namespace solutions\comment;

if (!\request::isPost())
	return $this->returnJson(['result' => false]);

if(!($model_name = \request::get('solution_comment_model')))
	return $this->returnJson(['result' => false, 'message' => 'error 1']);

if(md5($model_name.self::getConfig('secret')) != \request::get('sign'))
	return $this->returnJson(['result' => false, 'message' => 'error 2']);

if(!$comment_id = \request::getInt('comment_id'))
	return $this->returnJson(['result' => false, 'message' => 'error 3']);

$comment = new $model_name($comment_id);
if(!$comment->getId())
	return $this->returnJson(['result' => false, 'message' => 'error 4']);


switch(\request::get('type') == 'spam') {
	case 'spam':
		$field = 'complaints_ids_spam';
		$field_count = 'complaints_count_spam';
		$message_ok = __('Comment has been marked as spam.');
		$message_already = __('You already marked this comments as spam.');
		break;
	case 'complaint':
	default:
		$field = 'complaints_ids';
		$field_count = 'complaints_count';
		$message_ok = __('Your complaint has been accepted.');
		$message_already = __('You already sent complaint this comment.');
		break;
}

if(\solutions\user::current()) {
	$current_client_id = \solutions\user::current()->getId();
} else {
	$current_client_id = ip2long(\request::getClientIP());
}

if(in_array($current_client_id, explode(',', $comment->$field)))
	return $this->returnJson(['result' => false, 'message' => $message_already]);

$complaints_ids = array_merge([$current_client_id], explode(',', $comment->$field));
if(count($complaints_ids) > 1000) $complaints_ids = array_slice($complaints_ids, 0, 1000);
$comment->$field = implode(',', $complaints_ids);
$comment->$field_count = 1 + (int) $comment->$field_count;
$comment->save();

return $this->returnJson(['result' => true, 'message' => $message_ok]);
