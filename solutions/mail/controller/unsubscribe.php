<?
namespace solutions\mail;
$this->email = \helper::decode(\request::get('email'), \solutions\mail::getSecret());
if(!$this->email)
	return ;


$model = new unsubscribedModel();
$model->email = $this->email;
$model->find();

if($model->getId()) {
	if(\request::getInt('subscribe')) {
		$model->delete();
	}
} else {
	if(!\request::getInt('subscribe')) {
		$model->insert();
	}
}

if(\request::getInt('subscribe')) {
	$this->subscribed = true;
} else {
	$this->unsubscribed = true;
}

