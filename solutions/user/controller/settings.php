<?
namespace solutions\user;
use solutions\user;
use response;

if(!user::isAuthorized())
	response::redirect(self::getUrl('login'));