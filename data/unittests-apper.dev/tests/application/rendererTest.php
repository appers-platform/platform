<?
class rendererTest extends PHPUnit_Framework_TestCase {
	private $test_object;

	/**
	* @before
	*/
	public function testInit() {
		$this->test_object = new renderer();
	}

	public function testRenderFile() {
		$cached = false;
		try {
			$this->test_object->renderFile('nonexist_file');
		} catch(Exception $e) {
			$cached = true;
		}
		$this->assertTrue($cached);
		$this->test_object->renderFile(ROOT.'/tests/_data/empty.php');
	}

	public function test__get() {
		$this->assertNull($this->test_object->somevar);
		$this->test_object->somevar1 = 'abc';
		$this->assertTrue($this->test_object->somevar1 === 'abc');
		$this->assertTrue($this->test_object->__get('somevar1') === 'abc');
	}

	public function testGetLayoutPath() {
		$cached = false;
		try {
			$this->test_object->getLayoutPath('nonexist_file');
		} catch(Exception $e) {
			$cached = true;
		}
		$this->assertTrue($cached);
	}
}
