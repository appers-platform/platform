<?
\background::instance()->listen(\solutions\mail::SEND_QUEUE, function($arguments){
	call_user_func_array('\solutions\mail::directSend', $arguments);
});
