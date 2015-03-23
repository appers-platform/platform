<?
namespace solutions;

class highlighter extends solution {
	static public function out($content) {
		\js::addUrl('https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js', \js::GROUP_SOLUTIONS);
		print '<pre class="prettyprint">';
		print htmlspecialchars($content);
		print '</pre>';
		return '';
	}
}
