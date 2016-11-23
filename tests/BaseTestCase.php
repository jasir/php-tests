<?php

use Mockista\Registry;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
	/** @var Registry */
	protected $mockista;


	/**
	 * @param $name string
	 * @return string
	 */
	final public function getOutputFileName($name, $ext = 'out')
	{
		$pos = strrpos($name, '::');
		if ($pos >= 0) {
			$name = substr($name, $pos + 2);
		}
		$reflection = new ReflectionClass(get_called_class());
		return $reflection->getFileName() . ".{$name}.{$ext}";
	}


	/**
	 * @param $name
	 * @return string
	 */
	final public function getTestFileName($name)
	{
		return $this->getOutputFileName($name, 'expected');
	}


	/**
	 * @param $name string
	 * @param $data
	 */
	public function saveOutput($name, $data)
	{
		$fileName = $this->getOutputFileName($name);
		file_put_contents($fileName, var_export($data, true));
		return $data;
	}


	/**
	 * @param $name
	 * @return mixed
	 * @throws Exception
	 */
	public function getExpectedFromFile($name)
	{
		$fileName = $this->getOutputFileName($name, 'expected');
		if (!file_exists($fileName)) {
			throw new Exception("File {$fileName} not found.");
		}
		$content = file_get_contents($fileName);
		$expected = ["EVAL ERROR $fileName"];
		eval('$expected = ' . $content . ';');
		return $expected;
	}


	/**
	 * Removes unnecessary whitespaces for easier comparing
	 * @param string $s
	 * @returns string
	 */
	protected static function normalizeWhitespaces($s)
	{
		$s = str_replace(["\n", "\r", "\t"], ' ', $s);
		$s = trim($s);
		$s = preg_replace('/\s+/x', ' ', $s);
		return $s;
	}


	protected function setUp()
	{
		$this->mockista = new Registry();
	}


	protected function tearDown()
	{
		$this->mockista->assertExpectations();
	}


	/**
	 * @param $closure
	 * @param string $expectedExceptionClass
	 * @param null $expectedMessage
	 */
	protected function assertException($closure, $expectedExceptionClass = 'Exception', $expectedMessage = NULL)
	{
		try {
			call_user_func($closure);
		} catch (\Exception $e) {
			if (!$e instanceOf $expectedExceptionClass) {
				$this->assertInstanceOf($expectedExceptionClass, $e);
			}
			if ($expectedMessage) {
				$this->assertEquals($expectedMessage, $e->getMessage());
			}
			return;
		}
		$this->fail("Expected exception $expectedExceptionClass was not thrown");
	}


	/**
	 * Asserts entity data equals, ignores updatedBy, createdBy, updated, created
	 * @param $expected
	 * @param $actual
	 * @param null $message
	 */
	protected function assertEntityDataEquals($expected, $actual, $message = null)
	{

		if ($expected === null) {
			$this->assertNull($actual);
			return;
		}

		$this->assertEquals(
			$this->nullifySystemProperties($expected),
			$this->nullifySystemProperties($actual), $message
		);

	}


	/**
	 * @param $suffix
	 * @param $actual
	 * @param string $message
	 */
	protected function assertRoughlyEquals($suffix, $actual, $message = '')
	{
		$suffix = self::normalizeWhitespaces($suffix);
		$actual = self::normalizeWhitespaces($actual);
		$this->assertEquals($suffix, $actual, $message);
	}


	/**
	 * @param $suffix
	 * @param $actual
	 * @param string $message
	 */
	protected function assertRoughlyEndsWith($suffix, $actual, $message = '')
	{
		$suffix = self::normalizeWhitespaces($suffix);
		$actual = self::normalizeWhitespaces($actual);
		$actual = substr($actual, -strlen($suffix));
		parent::assertEquals($suffix, $actual, $message);
	}


	/**
	 * @param $array
	 * @return array
	 */
	private function nullifySystemProperties($array)
	{
		if (!is_array($array)) {
			return $array;
		}
		array_walk_recursive($array, function (&$value, $key) {
			if (in_array($key, ['updatedBy', 'createdBy', 'updated', 'created', 'zalozen'], true)) {
				$value = null;
			}
		});
		return $array;
	}


}
