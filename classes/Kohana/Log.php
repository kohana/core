<?php

namespace Kohana\Core;

use Exception;
use Kohana_Log_Buffer;
use Log_Writer;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Message logging with observer-based log writing.
 *
 * [!!] This class does not support extensions, only additional writers.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Log extends AbstractLogger implements Kohana_Log_Buffer {

	// Log message levels - Windows users see PHP Bug #18090
	const EMERGENCY = 0; // LOG_EMERG
	const ALERT     = 1; // LOG_ALERT
	const CRITICAL  = 2; // LOG_CRIT
	const ERROR     = 3; // LOG_ERR
	const WARNING   = 4; // LOG_WARNING
	const NOTICE    = 5; // LOG_NOTICE
	const INFO      = 6; // LOG_INFO
	const DEBUG     = 7; // LOG_DEBUG

	/**
	 * Numeric log level to string lookup table.
	 * @var array
	 */
	private static $_log_levels = array(
		self::EMERGENCY => LogLevel::EMERGENCY,
		self::ALERT     => LogLevel::ALERT,
		self::CRITICAL  => LogLevel::CRITICAL,
		self::ERROR     => LogLevel::ERROR,
		self::WARNING   => LogLevel::WARNING,
		self::NOTICE    => LogLevel::NOTICE,
		self::INFO      => LogLevel::INFO,
		self::DEBUG     => LogLevel::DEBUG,
	);

	/**
	 * @var  boolean  immediately flush when logs are added
	 */
	protected $flush_immediately = FALSE;

	/**
	 * @var  Log  Singleton instance container
	 */
	protected static $_instance;

	/**
	 * Get the singleton instance of this class and enable writing at shutdown.
	 *
	 *     $log = Log::instance();
	 *
	 * @return  Log
	 */
	public static function instance()
	{
		if (Log::$_instance === NULL)
		{
			// Create a new instance
			Log::$_instance = new \Log;

			// Write the logs at shutdown
			register_shutdown_function(array(Log::$_instance, 'flush'));
		}

		return Log::$_instance;
	}

	/**
	 * @var  array  list of added messages
	 */
	protected $_messages = array();

	/**
	 * @var  array  list of log writers
	 */
	protected $_writers = array();

	/**
	 * Attaches a log writer
	 *     $log->attach($writer);
	 *
	 * @param   Log_Writer  $writer     instance
	 * @return  Log
	 */
	public function attach(Log_Writer $writer)
	{

		$this->_writers["{$writer}"] = $writer;

		return $this;
	}

	/**
	 * Detaches a log writer. The same writer object must be used.
	 *
	 *     $log->detach($writer);
	 *
	 * @param   Log_Writer  $writer instance
	 * @return  Log
	 */
	public function detach(Log_Writer $writer)
	{
		// Remove the writer
		unset($this->_writers["{$writer}"]);

		return $this;
	}

	public static function get_levels()
	{
		return self::$_log_levels;
	}

	/**
	 * Validates and normalizes log levels to PSR-3 levels.
	 * Supports int, object and uppercase/lowercase string levels
	 *
	 * @param mixed $level
	 * @return string normalized PSR-3 level
	 * @throws InvalidArgumentException
	 */
	public static function to_psr_level($level)
	{
		// Check if log level exists in the self::$_log_levels array.
		if (is_int($level) AND isset(self::$_log_levels[$level]))
		{
			$level = self::$_log_levels[$level];
		}
		else if (is_string($level) AND in_array(strtolower($level), self::$_log_levels))
		{
			$level = strtolower($level);
		}
		else if (is_object($level) AND in_array((string) $level, self::$_log_levels))
		{
			$level = (string) $level;
		}
		else
		{
			throw new InvalidArgumentException('Undefined level "' . $level . '"');
		}

		return $level;
	}

	/**
	 * Validates and normalizes log levels to integer levels.
	 * Supports int, object and uppercase/lowercase string levels
	 *
	 * @param mixed $level
	 * @uses Log::to_psr_level
	 * @return int normalized integer level
	 * @throws InvalidArgumentException
	 */
	public static function to_int_level($level)
	{
		// first normalize to PSR-3 level
		$level = static::to_psr_level($level);

		return array_search($level, static::$_log_levels);
	}

	/**
	 * TRUE if Log is set to flush immediately, FALSE otherwise
	 *
	 * @return boolean
	 */
	public function get_immediate_flush()
	{
		return $this->flush_immediately;
	}

	/**
	 * Set/unset immediate writing
	 *
	 * @param boolean $flush_immediately
	 * @return Log
	 */
	public function set_immediate_flush($flush_immediately)
	{
		$this->flush_immediately = (bool) $flush_immediately;

		return $this;
	}

	/**
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 *     $log->add(Log::ERROR, 'Could not locate user: :user', array(
	 *         ':user' => $username,
	 *     ));
	 *
	 * @deprecated since version 3.4 in favor of Log::log
	 * @param   mixed  $level       level of message
	 * @param   string  $message     message body
	 * @param   array   $context      values to replace in the message
	 * @param   array   $additional  additional custom parameters to supply to the log writer
	 * @return  Log
	 */
	public function add($level, $message, array $context = NULL, array $additional = NULL)
	{
		/**
		 * normalize all parameters for PSR-3 compliance and
		 * in favor of Log::log method parameters
		 */

		// $context should always be an array
		if ($context === NULL)
		{
			$context = array();
		}
		else
		{
			// build a replacement array with braces around the context keys
			$replace = array();
			foreach (array_keys($context) as $key) {
				$replace[$key] = '{' . $key . '}';
			}
			// wrap variable names in message into braces
			$message = strtr($message, $replace);
		}

		// exceptions should go into $context, no more $additional parameter
		if (isset($additional['exception']))
		{
			$context['exception'] = $additional['exception'];
		}

		// use Log::log to process
		return $this->log($level, $message, $context);
	}

	/**
	 * Write and clear all of the messages.
	 *
	 *     $log->flush();
	 *
	 * @return  void
	 */
	public function flush()
	{
		if (empty($this->_messages))
		{
			// There is nothing to flush, move along
			return;
		}

		foreach ($this->_writers as $writer)
		{
			$writer->write($this->_messages);
		}

		// Reset the messages array
		$this->_messages = array();
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using Text::interpolate
	 *
	 *     $log->log(\Psr\Log\LogLevel::INFO, 'You look good, today.');
	 *
	 *     $log->log(Log::ERROR, 'Could not locate user: {username}', array(
	 *         'username' => $username,
	 *     ));
	 *
	 * @uses   Text::interpolate Inserts the replacement values into the message
	 * @param  mixed  $level    level of message
	 * @param  string $message  message body
	 * @param  array  $context  values to replace in the message
	 * @return Log
	 */
	public function log($level, $message, array $context = [])
	{
		// validate and normalize level
		$level = static::to_psr_level($level);

		// cast $message to string in compliance with PSR-3
		$message = (string) $message;

		if ($context)
		{
			// Insert the values into the message
			$message = Text::interpolate($message, $context);
		}

		// Grab a copy of the trace and sanitize $context['exception']
		if (
		  isset($context['exception']) AND
		  $context['exception'] instanceof Exception
		)
		{
			$trace = $context['exception']->getTrace();
		}
		else
		{
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

			// remove the call that comes from Psr\Log\AbstractLogger
			// in order to have consistent file and line elements in log
			$parent_class_file = (new ReflectionClass(get_parent_class()))->getFileName();
			if (isset($trace[0]['file']) AND  $parent_class_file === $trace[0]['file'])
			{
				$trace = array_slice($trace, 1);
			}

			// set $context['exception'] to FALSE in order to not repeat
			// the above if conditions again elsewhere
			$context['exception'] = FALSE;
		}

		$message = array(
			'time' => time(),
			'level' => $level,
			'body' => $message,
			'file' => isset($trace[0]['file']) ? $trace[0]['file'] : NULL,
			'line' => isset($trace[0]['line']) ? $trace[0]['line'] : NULL,
		);

		if ($context['exception'])
		{
			$message['exception'] = $context['exception'];
		}

		// add it to the local message array
		$this->_messages[] = $message;

		if ($this->flush_immediately)
		{
			// Write logs as they are added
			$this->flush();
		}

		return $this;
	}

}
