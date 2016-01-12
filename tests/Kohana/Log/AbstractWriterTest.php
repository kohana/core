<?php

/**
 * Tests Kohana Logging API - writing to Syslog
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 * @group kohana.core.logging.writer
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Log_AbstractWriterTest extends Unittest_TestCase {

	protected $dummy_log = [
		'time' => 1445267784,
		'level' => NULL, // will be set in make_dummy_log_entries
		'body' => 'dummy text',
		'line' => 10,
		'file' => '/path/to/file.php'
	];

	protected function make_dummy_log_entries(array $levels)
	{
		$logs = array();
		foreach ($levels as $level)
		{
			$dummy_log = $this->dummy_log;
			$dummy_log['level'] = $level;
			$logs[] = $dummy_log;
		}
		return $logs;
	}

	/**
	 * Data provider for test_filter
	 *
	 * @return array
	 */
	public function provider_filter()
	{
		return [
			// data set #0
			[
				// logs array
				$this->make_dummy_log_entries(Log::get_levels()),
				// filter to apply
				$levels = Log::get_levels(), // filter nothing
				// expected
				$this->make_dummy_written_logs($levels),
			],
			// data set #1
			[
				// logs array
				$this->make_dummy_log_entries(Log::get_levels()),
				// filter to apply
				$levels = [], // filter all
				// expected
				$this->make_dummy_written_logs($levels),
			],
			// data set #2
			[
				// logs array
				$this->make_dummy_log_entries(Log::get_levels()),
				// filter to apply
				$levels = ['info', 'debug'],
				// expected
				$this->make_dummy_written_logs($levels),
			],
		];
	}

	/**
	 * Tests Log_Writer::filter
	 *
	 * @test
	 * @dataProvider provider_filter
	 */
	public function test_filter($logs, $levels, $expected)
	{
		// Get a mock of the abstract Log_Writer
		$writer = $this->get_writer();

		$filter = new Log_Filter_PSRLevel($levels);

		$writer->attach_filter($filter);

		$writer->write($logs);

		$this->assertSame($expected, $this->get_written_logs());
	}

	abstract protected function get_writer();

	abstract protected function get_written_logs();

	abstract protected function make_dummy_written_logs(array $levels);
}
