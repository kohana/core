<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Message logging with observer-based log writing.
 *
 * [!!] This class does not support extensions, only additional writers.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log {

	/**
	 * @var  string  timestamp format for log entries
	 */
	public static $timestamp = 'Y-m-d H:i:s';

	/**
	 * @var  string  timezone for log entries
	 */
	public static $timezone;

	/**
	 * @var  boolean  immediately write when logs are added
	 */
	public static $write_on_add = FALSE;

	/**
	 * @var  Kohana_Log  Singleton instance container
	 */
	private static $_instance;

	/**
	 * Get the singleton instance of this class and enable writing at shutdown.
	 *
	 *     $log = Kohana_Log::instance();
	 *
	 * @return  Kohana_Log
	 */
	public static function instance()
	{
		if (self::$_instance === NULL)
		{
			// Create a new instance
			self::$_instance = new self;

			// Write the logs at shutdown
			register_shutdown_function(array(self::$_instance, 'write'));
		}

		return self::$_instance;
	}

	/**
	 * @var  array  list of added messages
	 */
	private $_messages = array();

	/**
	 * @var  array  list of log writers
	 */
	private $_writers = array();

	/**
	 * Attaches a log writer, and optionally limits the types of messages that
	 * will be written by the writer.
	 *
	 *     $log->attach($writer);
	 *
	 * @param   object  Kohana_Log_Writer instance
	 * @param   array   messages types to write
	 * @return  Kohana_Log
	 */
	public function attach(Kohana_Log_Writer $writer, array $types = NULL)
	{
		$this->_writers["{$writer}"] = array
		(
			'object' => $writer,
			'types' => $types
		);

		return $this;
	}

	/**
	 * Detaches a log writer. The same writer object must be used.
	 *
	 *     $log->detach($writer);
	 *
	 * @param   object  Kohana_Log_Writer instance
	 * @return  Kohana_Log
	 */
	public function detach(Kohana_Log_Writer $writer)
	{
		// Remove the writer
		unset($this->_writers["{$writer}"]);

		return $this;
	}

	/**
	 * Adds a message to the log. Replacement values must be passed in to be
	 * replaced using [strtr](http://php.net/strtr).
	 *
	 *     $log->add('error', 'Could not locate user: :user', array(
	 *         ':user' => $username,
	 *     ));
	 *
	 * @param   string  type of message
	 * @param   string  message body
	 * @param   array   values to replace in the message
	 * @return  Kohana_Log
	 */
	public function add($type, $message, array $values = NULL)
	{
		if ($values)
		{
			// Insert the values into the message
			$message = strtr($message, $values);
		}

		// Create a new message and timestamp it
		$this->_messages[] = array
		(
			'time' => Date::formatted_time('now', self::$timestamp, self::$timezone),
			'type' => $type,
			'body' => $message,
		);

		if (self::$write_on_add)
		{
			// Write logs as they are added
			$this->write();
		}

		return $this;
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
			if (empty($writer['types']))
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
					if (in_array($message['type'], $writer['types']))
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

} // End Kohana_Log
