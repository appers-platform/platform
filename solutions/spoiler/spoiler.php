<?
namespace solutions;

class spoiler extends solution {
	static public function start($title) {
?>
<div class="spoiler_solution">
<div class="panel panel-default">
	<div class="panel-heading">
	<a class="spoiler-trigger" href="javascript:void(0)" data-toggle="collapse"><?=$title?></a>
</div>
<div class="panel-collapse collapse out">
	<div class="panel-body">
		<p><?
		return '';
	}

	static public function end() {
		print '</p></div></div></div>';
		return '';
	}
}
