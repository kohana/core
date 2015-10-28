<?php

/**
 * Tests Kohana Logging API
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_LogTest extends Unittest_TestCase
{

	/**
	 * Tests that when a new logger is created the list of messages is initially
	 * empty
	 *
	 * @test
	 * @covers Log
	 */
	public function test_messages_is_initially_empty()
	{
		$logger = new Log;

		$this->assertAttributeSame(array(), '_messages', $logger);
	}

	/**
	 * Tests that when a new logger is created the list of writers is initially
	 * empty
	 *
	 * @test
	 * @covers Log
	 */
	public function test_writers_is_initially_empty()
	{
		$logger = new Log;

		$this->assertAttributeSame(array(), '_writers', $logger);
	}

	/**
	 * Test that attaching a log writer using an array of levels adds it to the array of log writers
	 *
	 * @TODO Is this test too specific?
	 *
	 * @test
	 * @covers Log::attach
	 */
	public function test_attach_attaches_log_writer_and_returns_this()
	{
		$logger = new Log;
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$this->assertSame($logger, $logger->attach($writer));

		$this->assertAttributeSame(
			array(spl_object_hash($writer) => $writer),
			'_writers',
			$logger
		);
	}

	/**
	 * When we call detach() we expect the specified log writer to be removed
	 *
	 * @test
	 * @covers Log::detach
	 */
	public function test_detach_removes_log_writer_and_returns_this()
	{
		$logger = new Log;
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$logger->attach($writer);

		$this->assertSame($logger, $logger->detach($writer));

		$this->assertAttributeSame(array(), '_writers', $logger);
	}

	/**
	 * Data provider for test_log_message
	 *
	 * @return array
	 */
	public function provider_log_message()
	{
		// a sample exception used in data sets
		$exception = new Exception('dummy exception text');
		return [
			// data set #0
			[
				\Psr\Log\LogLevel::DEBUG, // level
				'dummy message', // log text
				[], // context
				// expected raw log message array
				[
					'time' => time(), // to be reset within the test for HHVM
					'level' => \Psr\Log\LogLevel::DEBUG,
					'body' => 'dummy message',
					'line' => 0, // to be reset later for fuzzy testing
					'file' => __FILE__,
				]
			],
			// data set #1
			[
				\Psr\Log\LogLevel::ERROR, // level
				'dummy message', // log text
				['exception' => $exception], // context
				// expected raw log message array
				[
					'time' => time(), // to be reset within the test for HHVM
					'level' => \Psr\Log\LogLevel::ERROR,
					'body' => 'dummy message',
					'file' => NULL,
					'line' => 0, // to be reset later for fuzzy testing
					'exception' => $exception,
				]
			]
		];
	}

	/**
	 * Tests log message arrays have the expected elements
	 *
	 * @test
	 * @dataProvider provider_log_message
	 */
	public function test_log_message($level, $message, $context, $expected)
	{
		// initialize
		$logger = new Log;
		$writer = new Kohana_LogTest_Log_Writer_Memory();
		$logger->attach($writer);

		// Call log
		$logger->log($level, $message, $context);

		// HHVM runs Kohana tests slowly, we're going to reset the time here
		$expected['time'] = time();

		// Reset line number
		if (isset($context['exception'])) {
			$expected['line'] = NULL;
		} else {
			// fix expected line
			$expected_line = __LINE__;
			$expected['line'] = $expected_line;
		}

		$logger->flush();
		$actual = $writer->logs[0];

		// Assert
		$this->assertLogMessageEquals($expected, $actual);
	}
	/**
	 * Provider for test_specialized_vs_generic_methods
	 * and test_logging_abstract_logger
	 *
	 */
	public function provider_logging()
	{
		return [
			[
				'emergency',
				['emergency', Log::EMERGENCY, \Psr\Log\LogLevel::EMERGENCY,],
				'In case of emergency break glass.',
			],
			[
				'alert',
				['alert', Log::ALERT, \Psr\Log\LogLevel::ALERT,],
				'Alert: this is not a dialog box.',
			],
			[
				'critical',
				['critical', Log::CRITICAL, \Psr\Log\LogLevel::CRITICAL,],
				'Eye strain reaching critical levels!',
			],
			[
				'error',
				['error', Log::ERROR, \Psr\Log\LogLevel::ERROR,],
				'You must be doing something wrong.',
			],
			[
				'warning',
				['warning', Log::WARNING, \Psr\Log\LogLevel::WARNING,],
				'Too much cafeine blows up your brain.',
			],
			[
				'notice',
				['notice', Log::NOTICE, \Psr\Log\LogLevel::NOTICE,],
				'Not funny any more.',
			],
			[
				'info',
				['info', Log::INFO, \Psr\Log\LogLevel::INFO,],
				'Looking good today.',
			],
			[
				'debug',
				['debug', Log::DEBUG, \Psr\Log\LogLevel::DEBUG,],
				'Never again.',
			],
		];
	}

	/**
	 * Tests logging calls with a stub Log_Writer
	 *
	 * @test
	 * @dataProvider provider_logging
	 */
	public function test_specialized_vs_generic_methods($method, array $levels, $message)
	{
		// initialize
		$logger = new Log;
		$writer = new Kohana_LogTest_Log_Writer_Memory();
		$logger->attach($writer);

		// test logging with specialized method
		$logger->$method($message); /* Record line number for fuzzy test */ $expected_line = __LINE__;
		$logger->flush();
		$expected = $writer->logs[0];

		// reset writer's logs
		$writer->logs = array();

		// test logging with the generic log method
		foreach ($levels as $level) {
			$method = 'log';
			$logger->$method($level, $message); /* Record line number */ $actual_line = __LINE__;
		}
		$logger->flush();

		// assertions
		$line_delta = $actual_line - $expected_line;
		foreach ($writer->logs as $actual) {
			$this->assertLogMessageEquals($expected, $actual, $line_delta, 1);
		}
	}

	/**
	 * Tests that Log casts message to string
	 *
	 * @test
	 */
	public function test_log_casts_message_to_string()
	{
		// initialize
		$logger = new Log;
		$writer = new Kohana_LogTest_Log_Writer_Memory();
		$logger->attach($writer);

		// test logging with an object as message
		$object = new Kohana_LogTest_ToString();
		$logger->log(\Psr\Log\LogLevel::INFO, $object);
		$logger->flush();
		$actual = $writer->logs[0];

		// assertions
		$this->assertInternalType('string', $object->message);
		$this->assertInternalType('string', $actual['body']);
		$this->assertSame($object->message, $actual['body']);
	}

	/**
	 * Provider for test_immediate_flushing
	 */
	public function provider_immediate_flushing()
	{
		return [
			[
				TRUE, // flush immediately
				1, // $expected_number_of_logs
			],
			[
				FALSE, // do NOT flush immediately
				0, // $expected_number_of_logs
			]
		];
	}

	/**
	 * Tests that Log flushes immediately when immediate flushing is ON, otherwise
	 * it delays flushing after call to flush()
	 *
	 * @test
	 * @dataProvider provider_immediate_flushing
	 */
	public function test_immediate_flushing($flush_immediately, $expected_number_of_logs)
	{
		// initialize
		$logger = new Log;
		$writer = new Kohana_LogTest_Log_Writer_Memory();
		$logger->attach($writer);

		// Turn on immediate flushing
		$logger->set_immediate_flush($flush_immediately);

		// test logging with an object as message
		$logger->log(\Psr\Log\LogLevel::DEBUG, 'dummy message');

		// We will NOT call $logger->flush() here...

		$actual_number_of_logs = count($writer->logs);

		// assertions
		$this->assertSame($flush_immediately, $logger->get_immediate_flush());
		$this->assertSame($expected_number_of_logs, $actual_number_of_logs);

		// force flush and assert anew
		$logger->flush();
		$expected_number_of_logs = 1; // expect logs after forced flush
		$actual_number_of_logs = count($writer->logs); // recalculate actual
		$this->assertSame($expected_number_of_logs, $actual_number_of_logs);
	}

	/**
	 * Tests \Psr\Log\AbstractLogger
	 *
	 * @test
	 * @dataProvider provider_logging
	 */
	public function test_logging_abstract_logger($method, array $levels, $message)
	{
		$expected_level = $levels[2];
		$expected = array($expected_level, $message);
		$actual = NULL;

		// initialize and configure stub logger
		$stub_logger = $this->getMockBuilder('Psr\Log\AbstractLogger')
			->setMethods(['log'])
			->disableArgumentCloning()
			->getMock();
		$stub_logger->method('log')->will($this->returnCallback(
			function ($level, $message, array $context = []) use (&$actual) {
				$actual = array($level, $message);
			}));

		// Log with one of the specialized level methods
		$stub_logger->$method($message);

		// Assert
		$this->assertSame($expected, $actual);
	}

	/**
	 * A fuzzy log message assertion with line and time deltas
	 *
	 * @param array $expected expected log message
	 * @param array $actual actual log message
	 * @param int $line_delta Log message line number delta, for fuzzy equals
	 * @param int $time_delta Log message time delta, for fuzzy equals
	 */
	// @codingStandardsIgnoreStart
	public function assertLogMessageEquals($expected, $actual, $line_delta = 20, $time_delta = 10)
	// @codingStandardsIgnoreEnd
	{
		// level
		$this->assertSame($expected['level'], $actual['level']);
		// message
		$this->assertSame($expected['body'], $actual['body']);
		// file
		$this->assertSame($expected['file'], $actual['file']);
		// line (fuzzy equals)
		if (isset($expected['line'])) {
			$this->assertInternalType(gettype($expected['line']), $actual['line']);
		}
		$this->assertEquals($expected['line'], $actual['line'], '', $line_delta);
		// time (fuzzy equals)
		$this->assertInternalType(gettype($expected['time']), $actual['time']);
		$this->assertEquals($expected['time'], $actual['time'], '', $time_delta);
		// exception
		if(isset($expected['exception'])) {
			$this->assertSame($expected['exception'], $actual['exception']);
		}
	}
}

/**
 * A Log_Writer that appends logs to an internal array, used for testing
 */
class Kohana_LogTest_Log_Writer_Memory extends Log_Writer {

	public $logs = array();

	/**
	 *
	 * @param array $logs
	 */
	public function write(array $logs)
	{
		$this->logs = array_merge($this->logs, $logs);
	}

}
/**
 * A simple class with __toString method
 */
class Kohana_LogTest_ToString
{
	public $message = 'dummy string';

	/**
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->message;
	}
}
