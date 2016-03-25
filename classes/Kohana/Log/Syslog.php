<?php

namespace Kohana\Core\Log;

use Psr\Log\LogLevel;

/**
 * Syslog log writer.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Jeremy Bush
 * @copyright  (c) 2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Syslog extends LogWriter {

	/**
	 * @var  string  The syslog identifier
	 */
	protected $_ident;

	/**
	 * String log level to numeric lookup table.
	 *
	 * Windows users see PHP Bug #18090
	 *
	 * @var array
	 */
	protected $_log_levels = array(
		LogLevel::EMERGENCY => LOG_EMERG,
		LogLevel::ALERT     => LOG_ALERT,
		LogLevel::CRITICAL  => LOG_CRIT,
		LogLevel::ERROR     => LOG_ERR,
		LogLevel::WARNING   => LOG_WARNING,
		LogLevel::NOTICE    => LOG_NOTICE,
		LogLevel::INFO      => LOG_INFO,
		LogLevel::DEBUG     => LOG_DEBUG,
	);

	/**
	 * Creates a new syslog logger.
	 *
	 * @link    http://www.php.net/manual/function.openlog
	 *
	 * @param   string  $ident      syslog identifier
	 * @param   int     $facility   facility to log to
	 * @return  void
	 */
	public function __construct($ident = 'KohanaPHP', $facility = LOG_USER)
	{
		$this->_ident = $ident;

		// Open the connection to syslog
		openlog($this->_ident, LOG_CONS, $facility);
	}

	/**
	 * Writes each of the messages into the syslog.
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		$filtered_messages = $this->filter($messages);

		$strace_level = $this->get_strace_level();

		foreach ($filtered_messages as $message)
		{
			// convert the level into int level
			$level = $this->_log_levels[$message['level']];

			// write to syslog
			$this->_syslog($level, $message['body']);

			if (isset($message['exception']))
			{
				// convert PSR log level into syslog log level
				$level = $this->_log_levels[$strace_level];

				// write to syslog
				$this->_syslog($level, $message['exception']->getTraceAsString());
			}
		}
	}

	/**
	 * Proxy for the native syslog function - to allow mocking in unit tests
	 *
	 * @param int $priority a combination of the facility and the level
	 * @param string $message the message to send
	 *
	 * @return bool
	 * @see syslog
	 */
	protected function _syslog($priority, $message)
	{
		return syslog($priority, $message);
	}

	/**
	 * Closes the syslog connection
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		// Close connection to syslog
		closelog();
	}

}
