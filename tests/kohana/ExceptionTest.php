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
	 * Provides test data for test_constructor()
	 *
	 * @return array
	 */
	public function provider_constructor()
	{
		return array(
			array(array(''), '', 0),
			array(array(':a'), ':a', 0),

			array(array(':a', NULL), ':a', 0),
			array(array(':a', array()), ':a', 0),
			array(array(':a', array(':a' => 'b')), 'b', 0),
			array(array(':a :b', array(':a' => 'c', ':b' => 'd')), 'c d', 0),

			array(array(':a', NULL, 5), ':a', 5),
			// #3927
			array(array(':a', NULL, 'b'), ':a', 'b'),
		);
	}

	/**
	 * Tests Kohana_Kohana_Exception::__construct()
	 *
	 * @test
	 * @dataProvider provider_constructor
	 * @covers Kohana_Kohana_Exception::__construct
	 * @param array             $arguments          Arguments
	 * @param string            $expected_message   Value from getMessage()
	 * @param integer|string    $expected_code      Value from getCode()
	 */
	public function test_constructor($arguments, $expected_message, $expected_code)
	{
		switch (count($arguments))
		{
			case 1:
				$exception = new Kohana_Exception(reset($arguments));
			break;
			case 2:
				$exception = new Kohana_Exception(reset($arguments), next($arguments));
			break;
			default:
				$exception = new Kohana_Exception(reset($arguments), next($arguments), next($arguments));
		}

		$this->assertSame($expected_code, $exception->getCode());
		$this->assertSame($expected_message, $exception->getMessage());
	}

	/**
	 * Provides test data for test_handler()
	 * 
	 * @return array
	 */
	public function provider_handler()
	{
		return array(
			// $exception_type, $message, $is_cli, $expected
			array('Kohana_Exception', 'hello, world!', array('Kohana::$is_cli' => TRUE), FALSE, TRUE, "\nKohana_Exception [ 0 ]: hello, world! ~ SYSPATH/tests/kohana/ExceptionTest.php [ 110 ]\n", TRUE),
			array('Kohana_Exception', 'hello, world!', array('Kohana::$is_cli' => FALSE), FALSE, TRUE, 'hello, world!', FALSE),
			// # 3818
			array('Kohana_Exception', 'hello, world!', array('Request::$current' => Request::factory()), TRUE, TRUE, "\nKohana_Exception [ 0 ]: hello, world! ~ SYSPATH/tests/kohana/ExceptionTest.php [ 110 ]\n", TRUE),
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
