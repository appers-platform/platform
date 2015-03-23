<?
function rmDirRecursive($dir) {
	$file_info = new SplFileInfo($dir);
	$ri = new \IteratorIterator(
		new \DirectoryIterator($file_info->getRealPath())
	);
	foreach ($ri as $file) {
		if(is_dir($file->getPathname())) {
			if(in_array($file->getBasename(), ['..', '.']))
				continue;
			rmDirRecursive($file->getPathname());
		} else {
			unlink($file->getPathname());
		}
	}
	rmdir($dir);
}

function clearApperCache( $name = '' ) {
	if($name) {
		print "Clear cache for apper '{$name}'... \n";
		if(is_dir($dir_path = ROOT.'/cache/'.$name)) {
			rmDirRecursive($dir_path);
		}
	} else {
		print "Clear global cache... \n";
	}

	if(is_file($file_path = ROOT.'/cache/'.$name.'-load.php')) {
		unlink($file_path);
	}

	print "Done";
}

if(PROJECT) {
	clearApperCache(PROJECT);
} else {
	$file_info = new SplFileInfo(dirname(ROOT).'/appers');
	$ri = new \IteratorIterator(
		new \DirectoryIterator($file_info->getRealPath())
	);
	foreach ($ri as $file) {
		$name = $file->getBasename();
		if(substr($name, 0, 1) == '.')
			continue;
		if(!is_dir($file->getPathname()))
			continue;
		clearApperCache($name);
	}
	clearApperCache();
}