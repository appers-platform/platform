<?
#require_once ROOT.'/extLib/Spyc.php';

class yaml {
	static public function parse( $text ) {
		return Yaml\Yaml::parse( $text );
	}

	static public function parseFile( $file_path ) {
		return self::parse(file_get_contents($file_path));
	}

	static public function dump( array $data ) {
		return Yaml\Yaml::dump( $data );
	}
}