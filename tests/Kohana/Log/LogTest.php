<?php
use Kohana\Core\Log\LogWriter;

/**
 * Tests Kohana Logging API - extending the official abstract class for tests
 * \Psr\Log\Test\LoggerInterfaceTest
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.logging
 * @group kohana.core.logging.psr
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_LogTest extends \Psr\Log\Test\LoggerInterfaceTest
{
	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * The memory log writer to read back the logs from
	 *
	 * @var Kohana_Log_LoggerTest_Log_Writer_Memory
	 */
	protected $writer;

	/**
	 * {@inheritdoc}
	 */
	public function getLogger()
	{
		$this->log = new Log;
		$this->writer = new Kohana_Log_LoggerTest_Log_Writer_Memory();
		$this->log->attach($this->writer);
		return $this->log;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLogs()
	{
		// first, flush the logs to the writer
		$this->log->flush();

		// return collected logs
		return $this->writer->get_logs();
	}
}
/**
 * A Log_Writer that appends logs to an internal array, used for testing
 */
class Kohana_Log_LoggerTest_Log_Writer_Memory extends LogWriter {

	 protected $logs = array();

	/**
	 *
	 * @param array $logs
	 */
	public function write(array $logs)
	{
		// convert to format that \Psr\Log\Test\LoggerInterfaceTest expects
		array_walk($logs, function (&$log_entry){
			$log_entry = "{$log_entry['level']} {$log_entry['body']}";
		});

		// collect logs to internal array
		$this->logs = array_merge($this->logs, $logs);
	}

	/**
	 * Get collected logs
	 *
	 * @return array
	 */
	public function get_logs()
	{
		return $this->logs;
	}
}
