<?
namespace solutions;

class socialSharing extends solution {
	const FB_BUTTON = 1;
	const FB_BUTTON_COUNT = 2;
	const VK_BUTTON = 3;
	const TWITTER_BUTTON = 4;

	static protected $vk_share_inited = false;

	static public function initVkShare() {
		if(self::$vk_share_inited)
			return;
		self::$vk_share_inited = true;
		\js::addUrl('http://vk.com/js/api/share.js?90');
	}

	static public function show() {
		print '<div class="solution socialSharingSolution">';
		foreach(func_get_args() as $button) {
			switch($button) {
				case self::FB_BUTTON:
					if(!\solutions::checkInited('FBJsSDK')) {
						throw new \Exception('You mast enable solution "socialSharing" in config.');
					}
					print '<div class="fb-share-button" data-href="'.\request::getUrl().'" data-layout="button"></div>';
					break;
				case self::FB_BUTTON_COUNT:
					\solutions\FBJsSDK::init();
					print '<div class="fb-share-button" data-href="'.\request::getUrl().'" data-layout="button_count"></div>';
					break;
				case self::VK_BUTTON:
					self::initVkShare();
					print "<div class=\"VK\"><script type=\"text/javascript\">\n<!--\ndocument.write(VK.Share.button(false, {type:'round_nocount'}));\n-->\n</script></div>";
					break;
				case self::TWITTER_BUTTON:
					$tweet = __('Tweet');
					print <<<HTML
<a href="https://twitter.com/share" class="twitter-share-button" data-count="none">{$tweet}</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
HTML;
					break;
			}
		}
		print '</div>';
	}
}
