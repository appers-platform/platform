<?
namespace solutions;

use Exception;
use Imagick;
use request;

class iStorage extends solution {
	const RESIZE_TYPE_NONE = 0;
	const RESIZE_TYPE_RESIZE = 1;
	const RESIZE_TYPE_CROP = 2;

	/**
	 * @param $url string - Url of image
	 * @return string - hash in storage of solution (iStorage)
	 * @throws \Exception
	 */
	static public function uploadByUrl($url) {

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$data = curl_exec ($ch);
		curl_close ($ch);

		$try = 0;
		while(file_exists($tmp_path = $_SERVER['TMPDIR'].md5(time().microtime(true).rand(0,999)))) {
			if($try++ > 10) {
				throw new Exception('It very strange, but I can\'t generate tmp filename.');
			}
		}

		$fp = fopen($tmp_path, 'w');
		fwrite($fp, $data);
		fclose($fp);

		return self::moveUploadFile($tmp_path);
	}

	/**
	 * @param string $hash
	 * @param int $type
	 * @param null|int $width
	 * @param null|int $height
	 * @return string url
	 * @throws \Exception
	 */
	static public function getUrl($hash, $type = self::RESIZE_TYPE_NONE, $width = null, $height = null) {
		$height = abs((int) $height);
		$width = abs((int) $width);
		switch($type) {
			case self::RESIZE_TYPE_NONE:
				return '/iStorage/original/0/0/'.$hash;
				break;
			case self::RESIZE_TYPE_RESIZE:
				if(!$height || !$width)
					throw new Exception('Incorrect image size');
				return '/iStorage/resize/'.$width.'/'.$height.'/'.$hash;
				break;
			case self::RESIZE_TYPE_CROP:
				if(!$height || !$width)
					throw new Exception('Incorrect image size');
				return '/iStorage/crop/'.$width.'/'.$height.'/'.$hash;
				break;
		}
		throw new Exception('Unknown resize type');
	}

	/**
	 * @param string $hash
	 * @param int $type
	 * @param null|int $width
	 * @param null|int $height
	 * @return string url
	 * @throws \Exception
	 */
	static public function getAbsoluteUrl($hash, $type = self::RESIZE_TYPE_NONE, $width = null, $height = null) {
		return (request::getMethod() ?: 'http').'://'.request::getHost().self::getUrl($hash, $type, $width, $height);
	}

	/**
	 * @param array $arguments - arguments,
	 * @param string|array $success_callback
	 * @param string|array|null $fail_callback
	 * @return string html
	 */
	static public function htmlForm(array $arguments, $success_callback, $fail_callback = null) {
		return \solutions::controller('iStorage', 'form');
	}

	/**
	 * @param string $file_path
	 * @return string hash
	 * @throws \Exception
	 */
	static public function copyUploadFile($file_path) {
		if(!is_file($file_path)) {
			throw new Exception('File does not exist.');
		}
		if(!is_readable($file_path)) {
			throw new Exception('File does not readable.');
		}
		if(!getimagesize($file_path)) {
			throw new Exception('File does not readable as image');
		}

		$image = new Imagick($file_path);
		switch($format = self::getConfig('format')) {
			case 'jpeg':
				$image->setImageFormat('jpeg');
				$ext = 'jpg';
				if(!($quality = (int) self::getConfig('quality'))) {
					throw new Exception('Incorrect quality param.');
				}
				$image->setcompressionquality($quality);
				break;
			case 'png':
				$image->setImageFormat('png');
				$ext = 'png';
				break;
			default:
				throw new Exception('Unknown type "'.$format.'"');
				break;
		}

		$path = ROOT. '/solutions/iStorage/public/storage';
		$hash = self::generateHash();

		$dir = dirname($path.'/'.$hash);
		$elements = substr_count($hash, '/');
		$to_create = [];
		for($i = 0; $i < $elements; $i++) {
			if(!is_dir($dir)) {
				$to_create[] = $dir;
				$dir = dirname($dir);
			} else {
				break;
			}
		}

		foreach(array_reverse($to_create) as $dir) {
			mkdir($dir);
		}

		$image->writeImage($path.'/'.$hash.'.'.$ext);
		$image->clear();
		$image->destroy();

		return $hash.'.'.$ext;
	}

	/**
	 * @param string $file_path
	 * @return string hash
	 * @throws \Exception
	 */
	static public function moveUploadFile($file_path) {
		$result = self::copyUploadFile($file_path);
		unlink($file_path);

		return $result;
	}

	static public function generateHash() {
		$orig_md5 = md5(rand(0, 9999).microtime(true).time());
		$md5 = $orig_md5;
		for($i = 0; $i < 5; $i++) {
			$md5 = substr($md5, 0, $i*2 + $i).'/'.substr($md5, $i*2 + $i);
		}
		return strtolower( substr($md5, 1) );
	}
}
