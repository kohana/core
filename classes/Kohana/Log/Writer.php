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
	 * @var array log levels that this writer accepts to write
	 * 
	 * TRUE value indicates that the level at the key is writable
	 */
	private $write_levels = array(
		\Psr\Log\LogLevel::EMERGENCY => TRUE,
		\Psr\Log\LogLevel::ALERT     => TRUE,
		\Psr\Log\LogLevel::CRITICAL  => TRUE,
		\Psr\Log\LogLevel::ERROR     => TRUE,
		\Psr\Log\LogLevel::WARNING   => TRUE,
		\Psr\Log\LogLevel::NOTICE    => TRUE,
		\Psr\Log\LogLevel::INFO      => TRUE,
		\Psr\Log\LogLevel::DEBUG     => TRUE,
	);

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
	 * Returns an array mapping all PSR levels to boolean values indicating writability.
	 * A TRUE value indicates that the level at the key is writable for this writer.
	 *
	 * @return array map of PSR levels to boolean values - TRUE for writable
	 */
	public function get_psr_write_levels_map()
	{
		return $this->write_levels;
	}

	/**
	 * Gets the PSR log levels that this writer accepts to write
	 *
	 * @return string[] array of PSR levels
	 */
	public function get_psr_write_levels()
	{
		// Filter out the FALSE (not writable) values and return the keys
		return array_keys(array_filter($this->write_levels));
	}

	/**
	 * Gets the integer log levels that this writer accepts to write
	 *
	 * @uses Log_Writer::get_psr_write_levels
	 * @return int[] array of integer levels
	 */
	public function get_int_write_levels()
	{
		// get PSR string write levels
		$psr_write_levels = $this->get_psr_write_levels();

		// get the list of all log levels with integer keys
		$all_levels = Log::get_levels();

		// intersect and return the integer keys
		return array_keys(array_intersect($all_levels, $psr_write_levels));
	}

	/**
	 * Sets the levels of the logs that this writer should write
	 *
	 * @param array $write_levels
	 * @throws InvalidArgumentException
	 * @return Log_Writer
	 */
	public function set_write_levels(array $write_levels)
	{
		$write_levels = array_map('Log::to_psr_level', $write_levels);

		$callback = function(&$is_writable, $level) use ($write_levels) {
			$is_writable = in_array($level, $write_levels);
		};

		array_walk($this->write_levels, $callback);

		reset($this->write_levels);

		return $this;
	}

	/**
	 * Sets the levels' range of the logs that this writer should write
	 *
	 * @param mixed $min_level beginning log level
	 * @param mixed $max_level ending log level
	 * @throws InvalidArgumentException
	 * @uses Log_Writer::set_write_levels
	 * @return Log_Writer
	 */
	public function set_write_levels_range($min_level, $max_level)
	{
		$min_level = Log::to_int_level($min_level);
		$max_level = Log::to_int_level($max_level);

		if ( ! $max_level > $min_level)
		{
			throw InvalidArgumentException('maximum level should be greater than minimum level');
		}

		$this->set_write_levels(range($min_level, $max_level));

		return $this;
	}

	/**
	 * Filter the given log entries by log levels that this writer is configured
	 * to write.
	 *
	 * @param array $messages
	 * @return array Filtered messages
	 */
	public function filter(array $messages)
	{
		// if all writable levels are either TRUE or FALSE
		if(count(array_unique($this->write_levels)) === 1)
		{
			// Return all of the messages, or return an empty array 
			return current($this->write_levels) ? $messages : array();
		}

		// Filtered messages
		$filtered = array();

		foreach ($messages as $message)
		{
			if ($this->write_levels[$message['level']])
			{
				// Writer accepts this kind of message
				$filtered[] = $message;
			}
		}

		return $filtered;
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
