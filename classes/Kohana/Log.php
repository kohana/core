<?php
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
class Kohana_Log extends Psr\Log\AbstractLogger implements Kohana_Logger {

	// Log message levels - Windows users see PHP Bug #18090
	const EMERGENCY = LOG_EMERG;    // 0
	const ALERT     = LOG_ALERT;    // 1
	const CRITICAL  = LOG_CRIT;     // 2
	const ERROR     = LOG_ERR;      // 3
	const WARNING   = LOG_WARNING;  // 4
	const NOTICE    = LOG_NOTICE;   // 5
	const INFO      = LOG_INFO;     // 6
	const DEBUG     = LOG_DEBUG;    // 7

	/**
	 * Numeric log level to string lookup table.
	 * @var array
	 */
	protected $_log_levels = array(
		LOG_EMERG   => \Psr\Log\LogLevel::EMERGENCY,
		LOG_ALERT   => \Psr\Log\LogLevel::ALERT,
		LOG_CRIT    => \Psr\Log\LogLevel::CRITICAL,
		LOG_ERR     => \Psr\Log\LogLevel::ERROR,
		LOG_WARNING => \Psr\Log\LogLevel::WARNING,
		LOG_NOTICE  => \Psr\Log\LogLevel::NOTICE,
		LOG_INFO    => \Psr\Log\LogLevel::INFO,
		LOG_DEBUG   => \Psr\Log\LogLevel::DEBUG,
	);

	/**
	 * @var  boolean  immediately write when logs are added
	 */
	public static $write_on_add = FALSE;

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
			Log::$_instance = new Log;

			// Write the logs at shutdown
			register_shutdown_function(array(Log::$_instance, 'write'));
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
	 * Attaches a log writer, and optionally limits the levels of messages that
	 * will be written by the writer.
	 *
	 *     $log->attach($writer);
	 *
	 * @param   Log_Writer  $writer     instance
	 * @param   mixed       $levels     array of messages levels to write OR max level to write
	 * @param   integer     $min_level  min level to write IF $levels is not an array
	 * @return  Log
	 */
	public function attach(Log_Writer $writer, $levels = array(), $min_level = 0)
	{
		if ( ! is_array($levels))
		{
			$levels = range($min_level, $levels);
		}

		$this->_writers["{$writer}"] = array
		(
			'object' => $writer,
			'levels' => $levels
		);

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
	 *     $log->write();
	 *
	 * @return  void
	 */
	public function write()
	{
		if (empty($this->_messages))
		{
			// There is nothing to write, move along
			return;
		}

		// Import all messages locally
		$messages = $this->_messages;

		// Reset the messages array
		$this->_messages = array();

		foreach ($this->_writers as $writer)
		{
			if (empty($writer['levels']))
			{
				// Write all of the messages
				$writer['object']->write($messages);
			}
			else
			{
				// Filtered messages
				$filtered = array();

				foreach ($messages as $message)
				{
					if (in_array($message['level'], $writer['levels']))
					{
						// Writer accepts this kind of message
						$filtered[] = $message;
					}
				}

				// Write the filtered messages
				$writer['object']->write($filtered);
			}
		}
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
		// check if level is available
		if (is_int($level) AND array_key_exists($level, $this->_log_levels))
		{
			$level = $this->_log_levels[$level];
		}
		else if (is_string($level) AND in_array(strtolower($level), $this->_log_levels))
		{
			$level = strtolower($level);
		}
		else
		{
			throw new Psr\Log\InvalidArgumentException('Undefined level "' . $level . '"');
		}

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
		  is_object($context['exception']) AND
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
			if (isset($trace[0]['file']) AND  $parent_class_file === $trace[0]['file']) {
				$trace = array_slice($trace, 1);
			}

			// set $context['exception'] to FALSE in order to not repeat
			// the above if conditions again elsewhere
			$context['exception'] = FALSE;
		}

		// Create a new message
		$message = array(
			'time' => time(),
			'level' => $level,
			'body' => $message,
			'file' => isset($trace[0]['file']) ? $trace[0]['file'] : NULL,
			'line' => isset($trace[0]['line']) ? $trace[0]['line'] : NULL,
		);

		// add exception object to message, if available
		if ($context['exception'])
		{
			$message['exception'] = $context['exception'];
		}

		// add it to the local message array
		$this->_messages[] = $message;

		if (Log::$write_on_add)
		{
			// Write logs as they are added
			$this->write();
		}

		return $this;
	}

}
