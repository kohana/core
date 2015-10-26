<?php
/**
 * Log writer abstract class. All [Log] writers must extend this class.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Log_Writer {

	/**
	 * @var  string  date/time format for writing the timestamp of log entries.
	 *
	 * Defaults to Date::$timestamp_format
	 */
	private $timestamp_format;

	/**
	 * @var  string  timezone for this log writer
	 *
	 * Defaults to Date::$timezone, which defaults to date_default_timezone_get()
	 */
	private $timezone;

	/**
	 * @var  string  Level to use for stack traces
	 */
	private $strace_level = \Psr\Log\LogLevel::DEBUG;

	/**
	 * @var  string  default string format of the log entry
	 */
	private $format = "time --- level: body in file:line";

	/**
	 * Write an array of messages.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	abstract public function write(array $messages);

	/**
	 * Allows the writer to have a unique key when stored.
	 *
	 *     echo $writer;
	 *
	 * @return  string
	 */
	final public function __toString()
	{
		return spl_object_hash($this);
	}

	/**
	 * Gets the date/time format for writing a timestamp
	 * 
	 * @return string
	 */
	public function get_timestamp_format()
	{
		return $this->timestamp_format;
	}

	/**
	 * Sets the date/time format for writing a timestamp
	 * 
	 * @param string $timestamp_format
	 * @return Log_Writer
	 */
	public function set_timestamp_format($timestamp_format)
	{
		$this->timestamp_format = (string) $timestamp_format;
		
		return $this;
	}
	
	/**
	 * Gets the log level used when tracing an exception stack
	 * 
	 * @return string the PSR-3 log level used for stack tracing
	 */
	public function get_strace_level()
	{
		return $this->strace_level;
	}

	/**
	 * Sets the log level used when tracing an exception stack
	 * 
	 * @param mixed $level
	 * @throws InvalidArgumentException
	 * @return Log_Writer
	 */
	public function set_strace_level($level)
	{
		$this->strace_level = Log::to_psr_level($level);
		
		return $this;
	}
	
	/**
	 * Gets the default string format of the log entry
	 * 
	 * @return string
	 */
	public function get_format()
	{
		return $this->format;
	}

	/**
	 * Sets the default string format of the log entry
	 * 
	 * @param string $format
	 * @return Log_Writer
	 */
	public function set_format($format)
	{
		$this->format = (string) $format;
		
		return $this;
	}
	
	/**
	 * Gets the timezone of this Log_Writer
	 * 
	 * @return string
	 */
	public function get_timezone()
	{
		return $this->timezone;
	}

	/**
	 * Sets the timezone of this Log_Writer
	 * 
	 * @param string $timezone
	 * @throws InvalidArgumentException
	 * @return Log_Writer
	 */
	public function set_timezone($timezone)
	{
		if ( ! in_array($timezone, DateTimeZone::listIdentifiers()))
		{
			throw new InvalidArgumentException(
				'Argument 1 of set_timezone must be a valid timezone.'
			);
		}

		$this->timezone = $timezone;

		return $this;
	}
	
	/**
	 * Formats a log entry.
	 *
	 * @param   array   $message
	 * @param   string  $format
	 * @return  string
	 */
	public function format_message(array $message, $format = NULL)
	{
		$format = $format ? (string) $format : $this->format;
		
		$message['time'] = Date::formatted_time('@'.$message['time'], $this->timestamp_format, $this->timezone, TRUE);
		$message['level'] = strtoupper($message['level']);

		$string = strtr($format, array_filter($message, 'is_scalar'));

		if (isset($message['exception']))
		{
			// Re-use as much as possible, just resetting the body to the trace
			$message['body'] = $message['exception']->getTraceAsString();
			$message['level'] = strtoupper($this->strace_level);

			$string .= PHP_EOL.strtr($format, array_filter($message, 'is_scalar'));
		}

		return $string;
	}

}
