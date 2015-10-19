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
	 * Data provider for provider_log_message
	 *
	 * @return array
	 */
	public function provider_format_log()
	{
		// a sample exception used in data sets
		$exception = new Exception('dummy exception text');
		$exception_trace_as_string = strtoupper(Log_Writer::$strace_level)
				. ': '
				. $exception->getTraceAsString();

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
}