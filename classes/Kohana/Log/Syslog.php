<?php
/**
 * Syslog log writer.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Jeremy Bush
 * @copyright  (c) 2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_Syslog extends Log_Writer {

	/**
	 * @var  string  The syslog identifier
	 */
	protected $_ident;

	/**
	 * String log level to numeric lookup table.
	 * @var array
	 */
	protected $_log_levels = array(
		\Psr\Log\LogLevel::EMERGENCY => LOG_EMERG,
		\Psr\Log\LogLevel::ALERT     => LOG_ALERT,
		\Psr\Log\LogLevel::CRITICAL  => LOG_CRIT,
		\Psr\Log\LogLevel::ERROR     => LOG_ERR,
		\Psr\Log\LogLevel::WARNING   => LOG_WARNING,
		\Psr\Log\LogLevel::NOTICE    => LOG_NOTICE,
		\Psr\Log\LogLevel::INFO      => LOG_INFO,
		\Psr\Log\LogLevel::DEBUG     => LOG_DEBUG,
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

		foreach ($filtered_messages as $message)
		{
			// convert the level into int level
			$level = $this->_log_levels[$message['level']];

			// write to syslog
			$this->_syslog($level, $message['body']);

			if (isset($message['exception']))
			{
				// convert PSR log level into syslog log level
				$level = $this->_log_levels[Log_Writer::$strace_level];

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
