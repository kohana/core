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
	 * Provides test data for test_clean()
	 */
	public function provider_clean()
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
	 * @dataProvider provider_clean
	 */
	public function test_clean($input, $expected)
	{
		$this->assertSame($expected, UTF8::clean($input));
	}

	/**
	 * Provides test data for test_is_ascii()
	 */
	public function provider_is_ascii()
	{
		return array(
			array("\0", TRUE),
			array("\$eno\r", TRUE),
			array('Señor', FALSE),
			array(array('Se', 'nor'), TRUE),
			array(array('Se', 'ñor'), FALSE),
		);
	}

	/**
	 * Tests UTF8::is_ascii
	 *
	 * @test
	 * @dataProvider provider_is_ascii
	 */
	public function test_is_ascii($input, $expected)
	{
		$this->assertSame($expected, UTF8::is_ascii($input));
	}

	/**
	 * Provides test data for test_strip_ascii_ctrl()
	 */
	public function provider_strip_ascii_ctrl()
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
	 * @dataProvider provider_strip_ascii_ctrl
	 */
	public function test_strip_ascii_ctrl($input, $expected)
	{
		$this->assertSame($expected, UTF8::strip_ascii_ctrl($input));
	}

	/**
	 * Provides test data for test_strip_non_ascii()
	 */
	public function provider_strip_non_ascii()
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
	 * @dataProvider provider_strip_non_ascii
	 */
	public function test_strip_non_ascii($input, $expected)
	{
		$this->assertSame($expected, UTF8::strip_non_ascii($input));
	}

	/**
	 * Provides test data for test_str_ireplace()
	 */
	public function provider_str_ireplace()
	{
		return array(
			array('т', 't', 'cocoñuт', 'cocoñut'),
			array('Ñ', 'N', 'cocoñuт', 'cocoNuт'),
			array(array('т', 'Ñ'), array('t', 'N'), 'cocoñuт', 'cocoNut'),
		);
	}

	/**
	 * Tests UTF8::str_ireplace
	 *
	 * @test
	 * @dataProvider provider_str_ireplace
	 */
	public function test_str_ireplace($search, $replace, $subject, $expected)
	{
		$this->assertSame($expected, UTF8::str_ireplace($search, $replace, $subject));
	}

	/**
	 * Provides test data for test_strip_non_ascii()
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