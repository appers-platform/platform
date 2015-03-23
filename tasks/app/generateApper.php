<?
function recursive_copy($src, $dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				if ( $file != '.git')
					recursive_copy($src . '/' . $file,$dst . '/' . $file);
			} else {
				copy($src . '/' . $file, $dst . '/' . $file);
				chmod($dst . '/' . $file, fileperms($src . '/' . $file));
			}
		}
	}
	closedir($dir);
}

do {
	print "Enter Apper name (domain):\n> ";
	$name_ok = false;
	if($name = trim(cli::getLine())) {
		if(!preg_match("/^(?:[-A-Za-z0-9]+\\.)+[A-Za-z]{2,6}$/", $name)) {
			print 'Domain name is incorrect';
		} else {
			$path = dirname(ROOT).'/appers/'.$name;
			if(is_dir($path)) {
				print 'Apper already exist';
			} else {
				$name_ok = true;
			}
		}
	}
} while (!$name_ok);

if(!cli::confirm("Do you really want create apper?")) {
	return;
}

recursive_copy(ROOT.'/data/sample-apper.dev', dirname(ROOT).'/appers/'.$name);
$config = "<? return [
	'mysql'		=> [ 'db' 		=> '".str_replace('.', '_', $name)."' ],
	'memcache'	=> [ 'prefix'	=> '".str_replace('.', '_', $name)."' ],
	'gearman'	=> [ 'prefix'	=> '".str_replace('.', '_', $name)."' ],
	'secret'	=> '".\helper::generatePassword().\helper::generatePassword().\helper::generatePassword()."',
];
";
file_put_contents(dirname(ROOT).'/appers/'.$name.'/config/parent.php', $config);

print 'Done';

print "\n\nP.S.: Don't forget to put domain name to /etc/hosts file for local using\n";
