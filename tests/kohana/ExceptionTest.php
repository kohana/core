<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Exception Class
 *
 * @group kohana
 * @group kohana.exception
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_ExceptionTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_handler()
	 * 
	 * @return array
	 */
	public function provider_handler()
	{
		return array(
			// $exception_type, $message, $is_cli, $expected
			array('Kohana_Exception', 'hello, world!', array('Kohana::$is_cli' => TRUE), FALSE, TRUE, "\nKohana_Exception [ 0 ]: hello, world! ~ SYSPATH/tests/kohana/ExceptionTest.php [ 60 ]\n", TRUE),
			array('Kohana_Exception', 'hello, world!', array('Kohana::$is_cli' => FALSE), FALSE, TRUE, 'hello, world!', FALSE),
			// # 3818
			array('Kohana_Exception', 'hello, world!', array('Request::$current' => Request::factory()), TRUE, TRUE, "\nKohana_Exception [ 0 ]: hello, world! ~ SYSPATH/tests/kohana/ExceptionTest.php [ 60 ]\n", TRUE),
			array('ErrorException', 'hello, world!', array('Kohana::$is_cli' => TRUE), FALSE, TRUE, 'hello, world!', FALSE),
			// #3016
			array('Kohana_Exception', '<hello, world!>', array('Kohana::$is_cli' => FALSE), FALSE, TRUE, '&lt;hello, world!&gt;', FALSE),
		);
	}

	/**
	 * Tests Kohana_Exception::handler()
	 *
	 * @test
	 * @dataProvider provider_handler
	 * @covers Kohana_Exception::handler
	 * @param boolean $exception_type    Exception type to throw
	 * @param boolean $message           Message to pass to exception
	 * @param boolean $is_cli            Use cli mode?
	 * @param boolean $expected          Output for Kohana::exception_handler
	 * @param string  $expexcted_message What to look for in the output string
	 */
	public function test_handler($exception_type, $message, $env, $ajax, $expected, $expected_message, $test_complete_output)
	{
		$this->setEnvironment($env);

		if ($ajax)
		{
			$original_ajax = Request::$current->requested_with();
			Request::$current->requested_with('xmlhttprequest');
		}

		try
		{
			throw new $exception_type($message);
		}
		catch (Exception $e)
		{
			ob_start();
			$this->assertEquals($expected, Kohana_Exception::handler($e));
			$view = ob_get_contents();
			ob_clean();

			if ($test_complete_output)
			{
				$this->assertSame($expected_message, $view);
			}
			else
			{
				$this->assertContains($expected_message, $view);
			}
		}

		Kohana::$is_cli = TRUE;

		if ($ajax)
		{
			Request::$current->requested_with($original_ajax);
		}
	}

	/**
	 * Provides test data for test_text()
	 *
	 * @return array
	 */
	public function provider_text()
	{
		return array(
			array(new Kohana_Exception('foobar'), $this->dirSeparator('Kohana_Exception [ 0 ]: foobar ~ SYSPATH/tests/kohana/ExceptionTest.php [ '.__LINE__.' ]')),
		);
	}

	/**
	 * Tests Kohana_Exception::text()
	 *
	 * @test
	 * @dataProvider provider_text
	 * @covers Kohana_Exception::text
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_text($exception, $expected)
	{
		$this->assertEquals($expected, Kohana_Exception::text($exception));
	}
}
