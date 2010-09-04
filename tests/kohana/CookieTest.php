<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the cookie class
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
Class Kohana_CookieTest extends Kohana_Unittest_TestCase
{
	/**
	 * Provides test data for test_set()
	 *
	 * @return array
	 */
	function provider_set()
	{
		return array(
			array('foo', 'bar', NULL, TRUE),
			array('foo', 'bar', 10, TRUE),
		);
	}

	/**
	 * Tests cookie::set()
	 *
	 * @test
	 * @dataProvider provider_set
	 * @covers cookie::set
	 * @param mixed   $key      key to use
	 * @param mixed   $value    value to set
	 * @param mixed   $exp      exp to set
	 * @param boolean $expected Output for cookie::set()
	 */
	function test_set($key, $value, $exp, $expected)
	{
		$this->assertSame($expected, cookie::set($key, $value, $exp));
	}

	/**
	 * Provides test data for test_get()
	 *
	 * @return array
	 */
	function provider_get()
	{
		return array(
			array('foo', Cookie::salt('foo', 'bar').'~bar', 'bar'),
			array('bar', Cookie::salt('foo', 'bar').'~bar', NULL),
			array(NULL, Cookie::salt('foo', 'bar').'~bar', NULL),
		);
	}

	/**
	 * Tests cookie::set()
	 *
	 * @test
	 * @dataProvider provider_get
	 * @covers cookie::get
	 * @param mixed   $key      key to use
	 * @param mixed   $value    value to set
	 * @param boolean $expected Output for cookie::get()
	 */
	function test_get($key, $value, $expected)
	{
		// Force $_COOKIE
		if ($key !== NULL)
			$_COOKIE[$key] = $value;

		$this->assertSame($expected, cookie::get($key));
	}

	/**
	 * Provides test data for test_delete()
	 *
	 * @return array
	 */
	function provider_delete()
	{
		return array(
			array('foo', TRUE),
		);
	}

	/**
	 * Tests cookie::delete()
	 *
	 * @test
	 * @dataProvider provider_delete
	 * @covers cookie::delete
	 * @param mixed   $key      key to use
	 * @param boolean $expected Output for cookie::delete()
	 */
	function test_delete($key, $expected)
	{
		$this->assertSame($expected, cookie::delete($key));
	}

	/**
	 * Provides test data for test_salt()
	 *
	 * @return array
	 */
	function provider_salt()
	{
		return array(
			array('foo', 'bar', '795317c9df04d8061e6e134a9b3487dc9ad69117'),
		);
	}

	/**
	 * Tests cookie::salt()
	 *
	 * @test
	 * @dataProvider provider_salt
	 * @covers cookie::salt
	 * @param mixed   $key      key to use
	 * @param mixed   $value    value to salt with
	 * @param boolean $expected Output for cookie::delete()
	 */
	function test_salt($key, $value, $expected)
	{
		$this->assertSame($expected, cookie::salt($key, $value));
	}
}
