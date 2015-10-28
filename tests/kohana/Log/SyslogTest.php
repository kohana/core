<?php

/**
 * Tests Kohana Logging API - writing to Syslog
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 * @group kohana.core.logging.syslog
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_SyslogTest extends Unittest_TestCase {

	/**
	 * Provider for test_specialized_vs_generic_methods
	 *
	 */
	public function provider_syslog()
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
	 * @dataProvider provider_syslog
	 */
	public function test_syslog($method, array $levels, $message)
	{
		$logger = new Log;
		$writer = new Kohana_Log_SyslogTest_Syslog_Memory();
		$logger->attach($writer);

		// test logging with specialized method
		$logger->$method($message);
		$logger->flush();
		$expected = $writer->logs[0];

		// reset writer's logs
		$writer->logs = array();

		// test logging with the generic log method
		foreach ($levels as $level) {
			$method = 'log';
			$logger->$method($level, $message);
		}
		$logger->flush();

		// assertions
		foreach ($writer->logs as $actual) {
			// priority
			$this->assertSame($expected['priority'], $actual['priority']);
			// message
			$this->assertSame($expected['message'], $actual['message']);
		}
	}

}

/**
 * A Syslog writer that appends logs to an internal array, used for testing
 */
class Kohana_Log_SyslogTest_Syslog_Memory extends Log_Syslog {

	public $logs = array();

	/**
	 * Writes into public array $logs
	 *
	 * @param int $priority a combination of the facility and the level
	 * @param string $message the message to send
	 *
	 * @return bool
	 */
	protected function _syslog($priority, $message)
	{
		$log = [
			[
				'priority' => $priority,
				'message' => $message
			]
		];
		$this->logs = array_merge($this->logs, $log);
	}

}
