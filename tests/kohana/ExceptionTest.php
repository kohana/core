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
	 * Provides test data for testExceptionHandler()
	 *
	 * @return array
	 */
	public function provider_handler()
	{
		return array(
			// $exception_type, $message, $is_cli, $expected
			array('Kohana_Exception', 'hello, world!', TRUE, TRUE, 'hello, world!'),
			array('ErrorException', 'hello, world!', TRUE, TRUE, 'hello, world!'),
			// #3016
			array('Kohana_Exception', '<hello, world!>', FALSE, TRUE, '&lt;hello, world!&gt;'),
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
	 * @param boolean $expected          Output for Kohana_Exception::handler
	 * @param string  $expected_message  What to look for in the output string
	 */
	public function teste_handler($exception_type, $message, $is_cli, $expected, $expected_message)
	{
		try
		{
			Kohana::$is_cli = $is_cli;
			throw new $exception_type($message);
		}
		catch (Exception $e)
		{
			ob_start();
			$this->assertEquals($expected, Kohana_Exception::handler($e));
			$view = ob_get_contents();
			ob_clean();
			$this->assertContains($expected_message, $view);
		}

		Kohana::$is_cli = TRUE;
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
