<?
namespace solutions;

class FBJsSDK extends solution {
	static protected $app_id;

	static public function init() {
		if(parent::init()) {
			\event::addCallback('beforeControllerRender', [__CLASS__, '_addCode']);
			self::$app_id = self::getConfig('app_id', false);
		}
	}

	static public function _addCode() {
		$app_id = (self::$app_id) ? '&appId='.self::$app_id : '';
		\application::getController()->body_start = '
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v2.0'.$app_id.'";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, \'script\', \'facebook-jssdk\'));</script>
		';
	}

	static public function setAppId($app_id) {
		self::$app_id = $app_id;
	}

	static public function getAppId() {
		return self::$app_id;
	}
}
