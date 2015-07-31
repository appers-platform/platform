<?
$cmd = 'cd '.ROOT.' && git pull ';
if(is_dir(CONFIG_ROOT.'/.git')) {
	$cmd .= '&& cd '.CONFIG_ROOT.'; git pull';
}
if(PROJECT) {
	$cmd .= '&& cd '.PROJECT_ROOT.'; git pull';
}
cli::runCmdWithPrint($cmd);
cli::run(['app::clearCache'], false);
loader::generateCache();
cli::run(['sys::bgRestart'], false);
