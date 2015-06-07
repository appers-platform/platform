<?
namespace solutions;
use Exception;

class fs extends solution {
	static public function copy($src, $destination, $content_only = false) {
		if(!is_dir($destination) && !is_dir(dirname($destination))) {
			throw new Exception('Destination "'.$destination.'" is not dir ant can\'t be created.');
		}

		if(!$content_only) {
			$destination .= '/'.basename($src);
		}

		if(!is_dir($destination) && !mkdir($destination)) {
			throw new Exception('Destination "'.$destination.'" is not dir ant can\'t be created.');
		}

		if(!is_dir($src)) {
			return copy($src, $destination);
		}

		$dir = opendir($src);

		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
				    self::copy($src.'/'.$file, $destination.'/'.$file);
				}  else { 
				    copy($src.'/'.$file, $destination.'/'.$file);
			    } 
			} 
		} 

		closedir($dir); 

		return true;
	}
}
