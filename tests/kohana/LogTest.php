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
			array(spl_object_hash($writer) => array('object' => $writer, 'levels' => array())),
			'_writers',
			$logger
		);
	}

	/**
	 * Test that attaching a log writer using a min/max level adds it to the array of log writers
	 *
	 * @TODO Is this test too specific?
	 *
	 * @test
	 * @covers Log::attach
	 */
	public function test_attach_attaches_log_writer_min_max_and_returns_this()
	{
		$logger = new Log;
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$this->assertSame($logger, $logger->attach($writer, Log::NOTICE, Log::CRITICAL));

		$this->assertAttributeSame(
			array(spl_object_hash($writer) => array('object' => $writer, 'levels' => array(Log::CRITICAL, Log::ERROR, Log::WARNING, Log::NOTICE))),
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
	public function test_logging($method, array $levels, $message)
	{
		// initialize
		$logger = new Log;
		$writer = new Kohana_LogTest_Log_Writer_Memory();
		$logger->attach($writer);

		// test logging with specialized method
		$logger->$method($message);
		$logger->write();
		$expected = $writer->logs[0];
		// unset the first trace to the specialized method
		array_shift($expected['trace']);

		// reset writer's logs
		$writer->logs = array();

		// test logging with the generic log method
		foreach ($levels as $level) {
			$method = 'log';
			$logger->$method($level, $message);
		}
		$logger->write();

		// assertions
		foreach ($writer->logs as $log) {
			$this->assertSame($expected, $log);
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
