<?
class applicationLoaderTest extends PHPUnit_Framework_TestCase {
	public function testInit() {
		loader::init(true);
		$this->assertTrue(class_exists('loader'));
		$this->assertTrue(defined('DEFAULT_CONTENT_TYPE'));
	}

	public function testAutoLoad() {
		loader::flushCache();
		loader::flushCacheFile();

		$this->assertTrue(class_exists('application'));
		$this->assertTrue(class_exists('config'));
		$this->assertTrue(class_exists('context'));
		$this->assertTrue(class_exists('controller'));
		$this->assertTrue(class_exists('event'));

		loader::flushCache();
		$this->assertTrue(class_exists('renderer'));

		$prefix = 't'.md5(microtime());

		$this->assertTrue(class_exists("__solutions_{$prefix}Controller"));
		$this->assertTrue(is_subclass_of("__solutions_{$prefix}Controller", 'solutionLoaderController'));

		$this->assertTrue(class_exists("{$prefix}TModel"));
		$this->assertTrue(is_subclass_of("{$prefix}TModel", 'TModel'));

		$this->assertTrue(class_exists("{$prefix}Model"));
		$this->assertTrue(is_subclass_of("{$prefix}Model", 'model'));

		// @TODO: add test for /^([0-9a-zA-z_\\-\\\\]+)_controller$/

		$this->assertFalse(class_exists("{$prefix}_controller"));
		$this->assertFalse(class_exists("{$prefix}_solutionController"));
		//$this->assertTrue(is_subclass_of("__solutions_{$prefix}Controller", 'solutionLoaderController'));

		// @TODO: add test for /^([0-9a-zA-z_\\-\\\\]+)_solutionController$/
	}

	public function testGetMap() {
		$this->assertTrue(count(loader::getMap()) > 0);
		loader::flushCache();
		$this->assertTrue(count(loader::getMap()) > 0);
	}

	public function testIsFilesChanged() {
		$prev = loader::isFilesChanged();
		$this->assertTrue($prev == loader::isFilesChanged());
		
		loader::flushContentCacheFile();
		$prev = loader::isFilesChanged();
		$this->assertTrue($prev == loader::isFilesChanged());

		loader::flushContentCacheFile(false);
		$prev = loader::isFilesChanged();
		$this->assertTrue($prev == loader::isFilesChanged());

		loader::flushContentCache();
		$prev = loader::isFilesChanged();
		$this->assertTrue($prev == loader::isFilesChanged());
	}

	public function testGetClasses() {
		$code = '<?
		namespace test;
		class a {}
		trait b {}
		interface c {}
		';

		$classes = loader::getClasses($code);

		$this->assertContains('test\a', $classes);
		$this->assertContains('test\b', $classes);
		$this->assertContains('test\c', $classes);
	}

	public function testGenerateCache() {
		loader::generateCache(true);
		$file = ROOT.'/cache/'.PROJECT.'-load.php';
		$this->assertTrue(is_file($file));
		$this->assertTrue(is_readable($file));
		$file_content = file_get_contents($file);
		$this->assertTrue(strlen($file_content) > 0);
		$this->assertTrue(token_get_all($file_content) > 1);
	}

	public function testGetFilesListMd5() {
		$this->assertTrue(strlen(loader::getFilesListMd5(true)) == 32);
	}

	public function testGetFilesContentMd5() {
		$this->assertTrue(strlen(loader::getFilesContentMd5(true)) == 32);
	}

	public function testCheckFile() {
		loader::flushCache();

		$file = new SplFileInfo(ROOT.'/lib/application/application.php');
		$this->assertTrue(loader::checkFile($file));

		$file = new SplFileInfo(ROOT.'/extLib/less.inc.php');
		$this->assertFalse(loader::checkFile($file));
	}

	public function testGetClassesList() {
		$this->assertTrue(count(loader::getClassesList()) > 0);
	}
}
