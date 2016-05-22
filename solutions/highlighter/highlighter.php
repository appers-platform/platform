<?
namespace solutions;

class highlighter extends solution {
	static public function out($content) {
		\js::addUrl('https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js', \js::GROUP_SOLUTIONS);
		print '<pre class="prettyprint">';
		print htmlspecialchars($content);
		print '</pre>';
		return '';
	}
}
