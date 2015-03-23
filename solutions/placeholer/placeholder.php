<?
namespace solutions;

class placeholer extends solution {
	static public function link($text, $url, $num = 0) {
		$num = ((int) $num) ? ((int) $num) : '';
		return preg_replace([
			'/\\<link'.$num.'\\>(.*)\\<\\/link'.$num.'\\>/',
		], [
			'<a href=\''.$url.'\'>$1</a>'
		], $text);
	}
}
