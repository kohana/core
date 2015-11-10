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
class Kohana_Log_WriterTest extends Unittest_TestCase
{
	/**
	 * Data provider for test_format_log
	 *
	 * @return array
	 */
	public function provider_format_log()
	{
		// a sample exception used in data sets
		$exception = new Exception('dummy exception text');

		// expected exception trace, assuming the default trace level is unchanged
		$exception_trace_as_string = 'DEBUG: ' . $exception->getTraceAsString();

		return [
			// data set #0
			[
				// raw log message array
				[
					'time' => 1445267784,
					'level' => \Psr\Log\LogLevel::DEBUG,
					'body' => 'dummy text',
					'line' => 10,
					'file' => '/path/to/file.php',
				],
				// expected formatted string
				"2015-10-19 10:16:24 --- DEBUG: dummy text in /path/to/file.php:10"
			],
			// data set #1
			[
				// raw log message array
				[
					'time' => 1445268423,
					'level' => \Psr\Log\LogLevel::ERROR,
					'body' => 'dummy text',
					'file' => '/path/to/another/file.php',
					'line' => 20,
					'exception' => $exception,
				],
				// expected formatted string
				"2015-10-19 10:27:03 --- ERROR: dummy text in /path/to/another/file.php:20\n2015-10-19 10:27:03 --- "
				. $exception_trace_as_string
				. ' in /path/to/another/file.php:20'
			]
		];
	}

	/**
	 * Tests formatting log entries
	 *
	 * @test
	 * @dataProvider provider_format_log
	 */
	public function test_format_log($message, $expected)
	{
		// Get a mock of the abstract Log_Writer
		$writer = $this->getMockForAbstractClass('Log_Writer');

		// call format_message
		$actual = $writer->format_message($message);

		// Assert
		$this->assertSame($expected, $actual);
	}
	
	/**
	 * Data provider for test_getters_setters
	 *
	 * @return array
	 */
	public function provider_getters_setters()
	{
		return [
			['strace_level', Psr\Log\LogLevel::WARNING, FALSE],
			['strace_level', "Invalid", 'Psr\Log\InvalidArgumentException'],
			
			['format', 'body in file:line', FALSE],

			['timestamp_format', 'Y-m-d', FALSE],
			
			['timezone', 'Asia/Beirut', FALSE],
			['timezone', 'Invalid', '\InvalidArgumentException'],
		];
	}
	
	/**
	 * Tests getters setters
	 *
	 * @test
	 * @dataProvider provider_getters_setters
	 */
	public function test_getters_setters($property, $value, $exception)
	{
		if ($exception)
		{
			$this->setExpectedException($exception);
		}
		// Get a mock of the abstract Log_Writer
		$writer = $this->getMockForAbstractClass('Log_Writer');
		
		$setter = 'set_' . $property;
		$getter = 'get_' . $property;
		
		$writer->$setter($value);
		
		$this->assertSame($value, $writer->$getter());
	}

	/**
	 * Data provider for test_filter
	 *
	 * @return array
	 */
	public function provider_filter()
	{
		$make_logs = function(array $levels) {
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

		return [
			// data set #0
			[
				// logs array
				$make_logs(Log::get_levels()),
				// filter to apply
				$filter = Log::get_levels(), // filter nothing
				// expected
				$make_logs($filter),
			],
			// data set #1
			[
				// logs array
				$make_logs(Log::get_levels()),
				// filter to apply
				$filter = [], // filter all
				// expected
				$make_logs($filter),
			],
			// data set #2
			[
				// logs array
				$make_logs(Log::get_levels()),
				// filter to apply
				$filter = ['info', 'debug'],
				// expected
				$make_logs($filter),
			],
		];
	}

	/**
	 * Tests Log_Writer::filter
	 *
	 * @test
	 * @dataProvider provider_filter
	 */
	public function test_filter($logs, $filter, $expected)
	{
		// Get a mock of the abstract Log_Writer
		$writer = $this->getMockForAbstractClass('Log_Writer');

		$writer->set_filter($filter);

		$this->assertSame($expected, $writer->filter($logs));
	}
}
