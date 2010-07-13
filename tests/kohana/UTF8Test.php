<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');
/**
 * Tests Kohana_UTF8 class
 *
 * @group kohana
 * @group kohana.utf8
 *
 * @package    Unittest
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_UTF8Test extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for testClean()
	 */
	public function providerClean()
	{
		return array(
			array("\0", ''),
			array("→foo\021", '→foo'),
			array("\x7Fbar", 'bar'),
			array("\xFF", ''),
			array("\x41", 'A'),
		);
	}

	/**
	 * Tests UTF8::clean
	 *
	 * @test
	 * @dataProvider providerClean
	 */
	public function testClean($input, $expected)
	{
		$this->assertSame($expected, UTF8::clean($input));
	}

	/**
	 * Provides test data for testIsAscii()
	 */
	public function providerIsAscii()
	{
		return array(
			array("\0", TRUE),
			array("\$eno\r", TRUE),
			array('Señor', FALSE),
		);
	}

	/**
	 * Tests UTF8::is_ascii
	 *
	 * @test
	 * @dataProvider providerIsAscii
	 */
	public function testIsAscii($input, $expected)
	{
		$this->assertSame($expected, UTF8::is_ascii($input));
	}

	/**
	 * Provides test data for testStripAsciiCtrl()
	 */
	public function providerStripAsciiCtrl()
	{
		return array(
			array("\0", ''),
			array("→foo\021", '→foo'),
			array("\x7Fbar", 'bar'),
			array("\xFF", "\xFF"),
			array("\x41", 'A'),
		);
	}

	/**
	 * Tests UTF8::strip_ascii_ctrl
	 *
	 * @test
	 * @dataProvider providerStripAsciiCtrl
	 */
	public function testStripAsciiCtrl($input, $expected)
	{
		$this->assertSame($expected, UTF8::strip_ascii_ctrl($input));
	}

	/**
	 * Provides test data for testStripNonAscii()
	 */
	public function providerStripNonAscii()
	{
		return array(
			array("\0\021\x7F", "\0\021\x7F"),
			array('I ♥ cocoñùт', 'I  coco'),
		);
	}

	/**
	 * Tests UTF8::strip_non_ascii
	 *
	 * @test
	 * @dataProvider providerStripNonAscii
	 */
	public function testStripNonAscii($input, $expected)
	{
		$this->assertSame($expected, UTF8::strip_non_ascii($input));
	}

	/**
	 * Provides test data for testStripNonAscii()
	 */
	public function provider_ucwords()
	{
		return array(
			array('ExAmple', 'ExAmple'),
			array('i ♥ Cocoñùт', 'I ♥ Cocoñùт'),
		);
	}

	/**
	 * Tests UTF8::ucwords
	 *
	 * @test
	 * @dataProvider provider_ucwords
	 */
	public function test_ucwords($input, $expected)
	{
		$this->assertSame($expected, UTF8::ucwords($input));
	}
}