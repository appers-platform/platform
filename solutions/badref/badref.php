<?
namespace solutions;

class badref extends solution {
	static protected $blacklist = [
		'ilovevitaly.com',
		'contenthub.ru',
		'content-hub.ru',
		'cenoval.ru',
		'priceg.ru',
		'darodar.com',
		'o-o-6-o-o.com'
	];

	static public function init() {
		if(!parent::init())
			return false;
		\event::addCallback('beforeControllerRun', [__CLASS__, 'beforeControllerRun']);
		return true;
	}

	static public function beforeControllerRun() {
		foreach (self::$blacklist as $domain) {
			$domain = str_replace('.', '\\.', $domain);
			$pattern = '/^https?:\\/\\/([^\\/]+\.)?'.$domain.'(\\/.*)?$/';
			if(preg_match($pattern, \request::getReferer()))
				exit;
		}
	}
}
