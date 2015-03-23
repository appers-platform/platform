<?
namespace solutions;

class widget extends solution {
	static public function init() {
		if(!parent::init())
			return false;
		
		\js::setVar('solution_widget_text', [
			'ok'	=> __('Ok'),
			'yes'	=> __('Yes'),
			'no'	=> __('No')
		]);

		if($msg = \request::get('$$_solutions_widget_alert')) {
			\js::addSecondCallback('$$.solutions.widget.alert', $msg);
			$url_data = parse_url(\request::getUri());
			parse_str($url_data['query'], $url_params);
			unset($url_params['$$_solutions_widget_alert']);
			$url = $url_data['path'].'?'.http_build_query($url_params);
			\js::addSecondCallback('$$.solutions.widget.setAfterCloseCallback(function(){ document.location.href="'.$url.'"; });(function(){})', '');
		}

		return true;
	}
}
