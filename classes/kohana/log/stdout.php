<?php defined('SYSPATH') or die('No direct script access.');
/**
 * STDOUT log writer. Writes out messages to STDOUT.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Log_StdOut extends Log_Writer {
	
	/**
	 * @var  string  timestamp format for log entries
	 */
	public static $timestamp = 'Y-m-d H:i:s';

	/**
	 * @var  string  timezone for log entries
	 */
	public static $timezone;

	/**
	 * Writes each of the messages to STDOUT.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			// Writes out each message
			fwrite(STDOUT, PHP_EOL.$this->format_entry($message));
		}
	}

	/**
	 * Formats a log entry.
	 *
	 * @param   array   $message
	 * @return  string
	 */
	public function format_entry(array $message)
	{
		$string = Date::formatted_time($message['time'], Log_File::$timestamp, Log_File::$timezone).' --- '.$this->_log_levels[$message['level']].': '.$message['body'];

		if (isset($message['additional']['exception']))
		{
			$string .= PHP_EOL.$message['additional']['exception']->getTraceAsString();
		}

		return $string;
	}
} // End Kohana_Log_StdOut
