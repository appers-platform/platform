<?
namespace solutions;

class partialhide extends solution {
	static protected $i = 0;

	static public function start($title_more = null, $title_less = null, $height = 75) {
		self::$i++;
		\js::setVar('__solutions_partialhide'.self::$i, [
			'title_more'	=> $title_more ?: __('Show more'),
			'title_less'	=> $title_less ?: __('Show less'),
			'height' 		=> $height
		]);
?>
<div class="partialhide_solution" data-instid="<?=self::$i?>">
	<div class="wrap">
<?
		return '';
	}

	static public function end() {

		print '</div><div class="read-more"></div></div>';
		return '';
	}
}
