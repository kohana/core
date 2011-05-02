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
 * @license    http://kohanaframework.org/license
 */
class Kohana_CoreTest extends Kohana_Unittest_TestCase
{
	
	/**
	 * Provides test data for test_sanitize()
	 * 
	 * @return array
	 */
	public function provider_sanitize()
	{
		return array(
			// $value, $result
			array('foo', 'foo'),
			array("foo\r\nbar", "foo\nbar"),
			array("foo\rbar", "foo\nbar"),
			array("Is your name O\'reilly?", "Is your name O'reilly?")
		);
	}

	/**
	 * Tests Kohana::santize()
	 *
	 * @test
	 * @dataProvider provider_sanitize
	 * @covers Kohana::sanitize
	 * @param boolean $value  Input for Kohana::sanitize
	 * @param boolean $result Output for Kohana::sanitize
	 */
	public function test_sanitize($value, $result)
	{
		$this->setEnvironment(array('Kohana::$magic_quotes' => TRUE));

		$this->assertSame($result, Kohana::sanitize($value));
	}

	/**
	 * Passing FALSE for the file extension should prevent appending any extension.
	 * See issue #3214
	 *
	 * @test
	 * @covers  Kohana::find_file
	 */
	public function test_find_file_no_extension()
	{
		// EXT is manually appened to the _file name_, not passed as the extension
		$path = Kohana::find_file('classes', $file = 'kohana/core'.EXT, FALSE);

		$this->assertInternalType('string', $path);

		$this->assertStringEndsWith($file, $path);
	}

	/**
	 * If a file can't be found then find_file() should return FALSE if
	 * only a single file was requested, or an empty array if multiple files
	 * (i.e. configuration files) were requested
	 *
	 * @test
	 * @covers Kohana::find_file
	 */
	public function test_find_file_returns_false_or_array_on_failure()
	{
		$this->assertFalse(Kohana::find_file('configy', 'zebra'));

		$this->assertSame(array(), Kohana::find_file('configy', 'zebra', NULL, TRUE));
	}

	/**
	 * Kohana::list_files() should return an array on success and an empty array on failure
	 *
	 * @test
	 * @covers Kohana::list_files
	 */
	public function test_list_files_returns_array_on_success_and_failure()
	{
		$files = Kohana::list_files('config');

		$this->assertInternalType('array', $files);
		$this->assertGreaterThan(3, count($files));
		
		$this->assertSame(array(), Kohana::list_files('geshmuck'));
	}

	/**
	 * Tests Kohana::globals()
	 *
	 * @test
	 * @covers Kohana::globals
	 */
	public function test_globals_removes_user_def_globals()
	{
		$GLOBALS = array('hackers' => 'foobar','name' => array('','',''), '_POST' => array());

		Kohana::globals();

		$this->assertEquals(array('_POST' => array()), $GLOBALS);
	}

	/**
	 * Provides test data for testCache()
	 * 
	 * @return array
	 */
	public function provider_cache()
	{
		return array(
			// $value, $result
			array('foo', 'hello, world', 10),
			array('bar', NULL, 10),
			array('bar', NULL, -10),
		);
	}

	/**
	 * Tests Kohana::cache()
	 *
	 * @test
	 * @dataProvider provider_cache
	 * @covers Kohana::cache
	 * @param boolean $key      Key to cache/get for Kohana::cache
	 * @param boolean $value    Output from Kohana::cache
	 * @param boolean $lifetime Lifetime for Kohana::cache
	 */
	public function test_cache($key, $value, $lifetime)
	{
		Kohana::cache($key, $value, $lifetime);
		$this->assertEquals($value, Kohana::cache($key));
	}

	/**
	 * Provides test data for test_message()
	 * 
	 * @return array
	 */
	public function provider_message()
	{
		return array(
			// $value, $result
			array(':field must not be empty', 'validate', 'not_empty'),
			array(
				array(
					'alpha'         => ':field must contain only letters',
					'alpha_dash'    => ':field must contain only numbers, letters and dashes',
					'alpha_numeric' => ':field must contain only letters and numbers',
					'color'         => ':field must be a color',
					'credit_card'   => ':field must be a credit card number',
					'date'          => ':field must be a date',
					'decimal'       => ':field must be a decimal with :param1 places',
					'digit'         => ':field must be a digit',
					'email'         => ':field must be a email address',
					'email_domain'  => ':field must contain a valid email domain',
					'equals'        => ':field must equal :param1',
					'exact_length'  => ':field must be exactly :param1 characters long',
					'in_array'      => ':field must be one of the available options',
					'ip'            => ':field must be an ip address',
					'matches'       => ':field must be the same as :param1',
					'min_length'    => ':field must be at least :param1 characters long',
					'max_length'    => ':field must not exceed :param1 characters long',
					'not_empty'     => ':field must not be empty',
					'numeric'       => ':field must be numeric',
					'phone'         => ':field must be a phone number',
					'range'         => ':field must be within the range of :param1 to :param2',
					'regex'         => ':field does not match the required format',
					'url'           => ':field must be a url',
				),
				'validate', NULL, 
			),
		);
	}

	/**
	 * Tests Kohana::message()
	 *
	 * @test
	 * @dataProvider provider_message
	 * @covers Kohana::message
	 * @param boolean $expected Output for Kohana::message
	 * @param boolean $file     File to look in for Kohana::message
	 * @param boolean $key      Key for Kohana::message
	 */
	public function test_message($expected, $file, $key)
	{
		$this->assertEquals($expected, Kohana::message($file, $key));
	}

	/**
	 * Provides test data for test_error_handler()
	 * 
	 * @return array
	 */
	public function provider_error_handler()
	{
		return array(
			array(1, 'Foobar', 'foobar.php', __LINE__),
		);
	}

	/**
	 * Tests Kohana::error_handler()
	 *
	 * @test
	 * @dataProvider provider_error_handler
	 * @covers Kohana::error_handler
	 * @param boolean $code  Input for Kohana::sanitize
	 * @param boolean $error  Input for Kohana::sanitize
	 * @param boolean $file  Input for Kohana::sanitize
	 * @param boolean $line Output for Kohana::sanitize
	 */
	public function test_error_handler($code, $error, $file, $line)
	{
		$error_level = error_reporting();
		error_reporting(E_ALL);
		try
		{
			Kohana::error_handler($code, $error, $file, $line);
		}
		catch (Exception $e)
		{
			$this->assertEquals($code, $e->getCode());
			$this->assertEquals($error, $e->getMessage());
		}
		error_reporting($error_level);
	}

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
	 * Tests Kohana::debug()
	 *
	 * @test
	 * @dataProvider provider_debug
	 * @covers Kohana::debug
	 * @param boolean $thing    The thing to debug
	 * @param boolean $expected Output for Kohana::debug
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
	 * Tests Kohana::debug_path()
	 *
	 * @test
	 * @dataProvider provider_debug_path
	 * @covers Kohana::debug_path
	 * @param boolean $path     Input for Kohana::debug_path
	 * @param boolean $expected Output for Kohana::debug_path
	 */
	public function testDebugPath($path, $expected)
	{
		$this->assertEquals($expected, Kohana::debug_path($path));
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
	 * @param boolean $source   Input for Kohana::modules
	 * @param boolean $expected Output for Kohana::modules
	 */
	public function test_modules_sets_and_returns_valid_modules($source, $expected)
	{
		$modules = Kohana::modules();

		try
		{
			$this->assertEquals($expected, Kohana::modules($source));
		}
		catch(Exception $e)
		{
			Kohana::modules($modules);

			throw $e;
		}

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

		$this->assertInternalType('array', $modules);

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

		$this->assertInternalType('array', $include_paths);

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
