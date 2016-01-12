<?php

/**
 * Tests Kohana Exception Class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.exception
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
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
			// #3358
			array(array(':a', NULL, '3F000'), ':a', '3F000'),
			// #3404
			array(array(':a', NULL, '42S22'), ':a', '42S22'),
			// #3927
			array(array(':a', NULL, 'b'), ':a', 'b'),
			// #4039
			array(array(':a', NULL, '25P01'), ':a', '25P01'),
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
	 * Provides test data for test_text()
	 *
	 * @return array
	 */
	public function provider_text()
	{
		return array(
			array(new Kohana_ExceptionTest_Serializable_Exception('foobar'), $this->dirSeparator('Kohana_ExceptionTest_Serializable_Exception [ 0 ]: foobar ~ SYSPATH/tests/Kohana/ExceptionTest.php [ ' . __LINE__ . ' ]')),
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

	/**
	 * Test if Kohana_Exception logs exceptions
	 *
	 * @test
	 * @dataProvider provider_text
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_logs_exception($exception, $expected)
	{
		Kohana::$log = new Log();
		$writer = new Kohana_ExceptionTest_Log_Writer_Memory();
		Kohana::$log->attach($writer);
		Kohana_Exception::log($exception);
		$this->assertSame($expected, $writer->messages[0]['body']);
	}

	/**
	 * Test if Kohana_Exception logs exceptions if Kohana::$log is not
	 * a Kohana_Log
	 *
	 * @test
	 * @dataProvider provider_text
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_log_not_kohana_log($exception, $expected)
	{
		Kohana::$log = new Kohana_ExceptionTest_Log_Psr_Mock();
		Kohana_Exception::log($exception);
		$this->assertSame($expected, Kohana::$log->logs[0]['message']);
	}

	/**
	 * Test if Kohana_Exception fails silently when Kohana::$log is unassigned
	 *
	 * @test
	 * @dataProvider provider_text
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_fails_silently_when_log_not_assigned($exception, $expected)
	{
		Kohana::$log = NULL;

		// Generic assertion that it is failing silently
		// Should it raise any exception, the test will fail as expected.
		$this->assertNull(Kohana_Exception::log($exception));
	}


}

/**
 * A Log_Writer that appends messages to an internal array, used for testing
 */
class Kohana_ExceptionTest_Log_Writer_Memory extends Log_Writer
{
	public $messages = array();
	/**
	 *
	 * @param array $messages
	 */
	public function write(array $messages)
	{
		$this->messages = array_merge($this->messages, $messages);
	}
}

/**
 * A PSR-3 compliant stub logger
 */
class Kohana_ExceptionTest_Log_Psr_Mock extends Psr\Log\AbstractLogger {

	/**
	 * @var array registry of logs
	 */
	public $logs = array();

	/**
	 * a stub for logging
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = array())
	{
		$this->logs[] = [
			'level' => $level,
			'message' => $message,
			'context' => $context
		  ];
	}

}

/**
 * A Serializable Exception
 * An exception can not be serialized when its stack trace contains a closure.
 * This class implements Serializable without serializing the stack trace.
 *
 * @link http://fabien.potencier.org/php-serialization-stack-traces-and-exceptions.html PHP Serialization, Stack Traces, and Exceptions
 */
class Kohana_ExceptionTest_Serializable_Exception extends Kohana_Exception implements Serializable {

	public function serialize()
	{
		return serialize(array($this->message, $this->code, $this->file, $this->line));
	}

	public function unserialize($serialized)
	{
		list($this->message, $this->code, $this->file, $this->line) = unserialize($serialized);
	}

}
