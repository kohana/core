<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group kohana
 * @group kohana.core
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_DebugTest extends Kohana_Unittest_TestCase
{

	/**
	 * Provides test data for test_debug()
	 * 
	 * @return array
	 */
	public function provider_debug()
	{
		return array(
			// $exception_type, $message, $is_cli, $expected
			array(array('foobar'), "<pre class=\"debug\"><small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span></pre>"),
		);
	}

	/**
	 * Tests Debug::vars()
	 *
	 * @test
	 * @dataProvider provider_debug
	 * @covers Debug::vars
	 * @param boolean $thing    The thing to debug
	 * @param boolean $expected Output for Debug::vars
	 */
	public function testdebug($thing, $expected)
	{
		$this->assertEquals($expected, Kohana::debug($thing));
	}

	/**
	 * Provides test data for testDebugPath()
	 * 
	 * @return array
	 */
	public function provider_debug_path()
	{
		return array(
			array(
				Kohana::find_file('classes', 'kohana'), 
				'SYSPATH'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'kohana.php'
			),
			array(
				Kohana::find_file('classes', $this->dirSeparator('kohana/unittest/runner')), 
				$this->dirSeparator('MODPATH/unittest/classes/kohana/unittest/runner.php')
			),
		);
	}

	/**
	 * Tests Debug::path()
	 *
	 * @test
	 * @dataProvider provider_debug_path
	 * @covers Debug::path
	 * @param boolean $path     Input for Debug::path
	 * @param boolean $expected Output for Debug::path
	 */
	public function testDebugPath($path, $expected)
	{
		$this->assertEquals($expected, Debug::path($path));
	}

	/**
	 * Provides test data for test_modules_sets_and_returns_valid_modules()
	 * 
	 * @return array
	 */
	public function provider_modules_sets_and_returns_valid_modules()
	{
		return array(
			array(array(), array()),
			array(array('unittest' => MODPATH.'fo0bar'), array()),
			array(array('unittest' => MODPATH.'unittest'), array('unittest' => $this->dirSeparator(MODPATH.'unittest/'))),
		);
	}

	/**
	 * Tests Kohana::modules()
	 *
	 * @test
	 * @dataProvider provider_modules_sets_and_returns_valid_modules
	 * @covers Kohana::modules
	 * @param boolean $source   Input for Kohana::modules
	 * @param boolean $expected Output for Kohana::modules
	 */
	public function test_modules_sets_and_returns_valid_modules($source, $expected)
	{
		$modules = Kohana::modules();

		$this->assertEquals($expected, Kohana::modules($source));

		Kohana::modules($modules);
	}

	/**
	 * To make the tests as portable as possible this just tests that 
	 * you get an array of modules when you can Kohana::modules() and that
	 * said array contains unittest
	 *
	 * @test
	 * @covers Kohana::modules
	 */
	public function test_modules_returns_array_of_modules()
	{
		$modules = Kohana::modules();

		$this->assertType('array', $modules);

		$this->assertArrayHasKey('unittest', $modules);
	}

	/**
	 * Tests Kohana::include_paths()
	 *
	 * The include paths must contain the apppath and syspath
	 * @test
	 * @covers Kohana::include_paths
	 */
	public function test_include_paths()
	{
		$include_paths = Kohana::include_paths();
		$modules       = Kohana::modules();

		$this->assertType('array', $include_paths);

		// We must have at least 2 items in include paths (APP / SYS)
		$this->assertGreaterThan(2, count($include_paths));
		// Make sure said paths are in the include paths
		// And make sure they're in the correct positions
		$this->assertSame(APPPATH, reset($include_paths));
		$this->assertSame(SYSPATH, end($include_paths));
		
		foreach($modules as $module)
		{
			$this->assertContains($module, $include_paths);
		}
	}

	/**
	 * Provides test data for test_exception_text()
	 * 
	 * @return array
	 */
	public function provider_exception_text()
	{
		return array(
			array(new Kohana_Exception('foobar'), $this->dirSeparator('Kohana_Exception [ 0 ]: foobar ~ SYSPATH/tests/kohana/CoreTest.php [ '.__LINE__.' ]')),
		);
	}

	/**
	 * Tests Kohana::exception_text()
	 *
	 * @test
	 * @dataProvider provider_exception_text
	 * @covers Kohana::exception_text
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_exception_text($exception, $expected)
	{
		$this->assertEquals($expected, Kohana::exception_text($exception));
	}

	/**
	 * Provides test data for test_dump()
	 * 
	 * @return array
	 */
	public function provider_dump()
	{
		return array(
			array('foobar', 128, '<small>string</small><span>(6)</span> "foobar"'),
			array('foobar', 2, '<small>string</small><span>(6)</span> "fo&nbsp;&hellip;"'),
			array(NULL, 128, '<small>NULL</small>'),
			array(TRUE, 128, '<small>bool</small> TRUE'),
			array(array('foobar'), 128, "<small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span>"),
			array(new StdClass, 128, "<small>object</small> <span>stdClass(0)</span> <code>{\n}</code>"),
			array("fo\x6F\xFF\x00bar\x8F\xC2\xB110", 128, '<small>string</small><span>(10)</span> "foobar±10"'),
		);
	}

	/**
	 * Tests Kohana::dump()
	 *
	 * @test
	 * @dataProvider provider_dump
	 * @covers Kohana::dump
	 * @covers Kohana::_dump
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_dump($input, $length, $expected)
	{
		$this->assertEquals($expected, Kohana::dump($input, $length));
	}
}
