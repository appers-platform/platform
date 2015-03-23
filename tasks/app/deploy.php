<?
$deploy_server = config::get('deploy')['server'];
$deploy_path = config::get('deploy')['path'];

if(!PROJECT) {
	print "You can deploy only from project";
	return;
}

if(!$deploy_server || !$deploy_path) {
?>
You should set param 'deploy'->'server' and 'deploy'->'path' in config.
For example, to connect to sshuser@example.com and deploy project with path '/home/www/appers/example.com', config will be:

'deploy'			=> [
	'server'	=>	'sshuser@example.com',
	'path'		=>	'/home/www/appers/example.com'
]

<?
	return;
}

$cmd = 'ssh '.$deploy_server.' "'.$deploy_path.'/exec app::update"';

cli::runCmdWithPrint($cmd);
