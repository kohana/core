<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group kohana
 * @group kohana.debug
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_DebugTest extends Unittest_TestCase
{

	/**
	 * Provides test data for test_debug()
	 *
	 * @return array
	 */
	public function provider_vars()
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
	 * @dataProvider provider_vars
	 * @covers Debug::vars
	 * @param boolean $thing    The thing to debug
	 * @param boolean $expected Output for Debug::vars
	 */
	public function test_var($thing, $expected)
	{
		$this->assertEquals($expected, Debug::vars($thing));
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
				SYSPATH.'classes'.DIRECTORY_SEPARATOR.'kohana'.EXT,
				'SYSPATH'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'kohana.php'
			),
			array(
				MODPATH.$this->dirSeparator('unittest/classes/kohana/unittest/runner').EXT,
				$this->dirSeparator('MODPATH/unittest/classes/kohana/unittest/runner').EXT
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
	public function test_debug_path($path, $expected)
	{
		$this->assertEquals($expected, Debug::path($path));
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
	 * Tests Debug::dump()
	 *
	 * @test
	 * @dataProvider provider_dump
	 * @covers Debug::dump
	 * @covers Debug::_dump
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_dump($input, $length, $expected)
	{
		$this->assertEquals($expected, Debug::dump($input, $length));
	}
}
