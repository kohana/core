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
	 * Provider for test_syslog
	 *
	 */
	public function provider_syslog()
	{
		// a sample exception used in data sets
		$exception = new Exception('dummy exception text');

		return [
			// data set #0
			[
				// raw log messages
				[
					[
						'time' => 1445267784,
						'level' => \Psr\Log\LogLevel::DEBUG,
						'body' => 'dummy text',
						'line' => 10,
						'file' => '/path/to/file.php',
					]
				],
				// expected syslog entries
				[
					[LOG_DEBUG, 'dummy text']
				]
			],
			// data set #1
			[
				// raw log messages
				[
					[
						'time' => 1445268423,
						'level' => \Psr\Log\LogLevel::ERROR,
						'body' => 'another dummy text',
						'file' => '/path/to/another/file.php',
						'line' => 20,
						'exception' => $exception,
					]
				],
				[
					[LOG_ERR, 'another dummy text'],
					[LOG_DEBUG, $exception->getTraceAsString()]
				]
			]
		];
	}

	/**
	 * Tests logging with the syslog writer
	 *
	 * @test
	 * @dataProvider provider_syslog
	 */
	public function test_syslog(array $log_entries, array $expected)
	{
		$writer = new Kohana_Log_SyslogTest_Syslog_Memory();

		$writer->write($log_entries);

		// assertions
		$this->assertSame($expected, $writer->get_logs());
	}

	/**
	 * Data provider for test_filter
	 *
	 * @return array
	 */
	public function provider_filter()
	{
		$psr_to_syslog = array(
			\Psr\Log\LogLevel::EMERGENCY => LOG_EMERG,
			\Psr\Log\LogLevel::ALERT => LOG_ALERT,
			\Psr\Log\LogLevel::CRITICAL => LOG_CRIT,
			\Psr\Log\LogLevel::ERROR => LOG_ERR,
			\Psr\Log\LogLevel::WARNING => LOG_WARNING,
			\Psr\Log\LogLevel::NOTICE => LOG_NOTICE,
			\Psr\Log\LogLevel::INFO => LOG_INFO,
			\Psr\Log\LogLevel::DEBUG => LOG_DEBUG,
		);

		$make_logs = function(array $levels)
		{
			$logs = array();
			foreach ($levels as $level)
			{
				$logs[] = [
					'time' => 1445267784,
					'level' => $level,
					'body' => 'dummy text',
					'line' => 10,
					'file' => '/path/to/file.php',
				];
			}
			return $logs;
		};

		$make_syslogs = function(array $levels) use ($psr_to_syslog)
		{
			$logs = array();
			foreach ($levels as $level)
			{
				$logs[] = [$psr_to_syslog[$level], 'dummy text'];
			}
			return $logs;
		};

		return [
			// data set #0
			[
				// logs array
				$make_logs(Log::get_levels()),
				// filter to apply
				$levels = Log::get_levels(), // filter nothing
				// expected
				$make_syslogs($levels),
			],
			// data set #1
			[
				// logs array
				$make_logs(Log::get_levels()),
				// filter to apply
				$levels = [], // filter all
				// expected
				$make_syslogs($levels),
			],
			// data set #2
			[
				// logs array
				$make_logs(Log::get_levels()),
				// filter to apply
				$levels = ['info', 'debug'],
				// expected
				$make_syslogs($levels),
			],
		];
	}

	/**
	 * Tests Log_Syslog::filter
	 *
	 * @test
	 * @dataProvider provider_filter
	 */
	public function test_filter($logs, $levels, $expected)
	{
		$writer = new Kohana_Log_SyslogTest_Syslog_Memory();

		$filter = new Log_Filter_PSRLevel($levels);

		$writer->attach_filter($filter);

		$writer->write($logs);

		$this->assertSame($expected, $writer->get_logs());
	}
}

/**
 * A Syslog writer that appends logs to an internal array, used for testing
 */
class Kohana_Log_SyslogTest_Syslog_Memory extends Log_Syslog {

	private $logs = array();

	public function get_logs()
	{
		return $this->logs;
	}

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
		$this->logs[] = [$priority, $message];
	}
}
