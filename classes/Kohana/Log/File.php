<?php
/**
 * File log writer. Writes out messages and stores them in a YYYY/MM directory.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_File extends Log_Writer {

	/**
	 * @var  string  Directory to place log files in
	 */
	protected $_directory;

	/**
	 * Creates a new file logger. Checks that the directory exists and
	 * is writable.
	 *
	 *     $writer = new Log_File($directory);
	 *
	 * @param   string  $directory  log directory
	 * @return  void
	 */
	public function __construct($directory)
	{
		if ( ! is_dir($directory) OR ! is_writable($directory))
		{
			throw new Kohana_Exception('Directory :dir must be writable',
				array(':dir' => Debug::path($directory)));
		}

		// Determine the directory path
		$this->_directory = $directory.DIRECTORY_SEPARATOR;
	}

	/**
	 * Writes each of the messages into the log file. The log file will be
	 * appended to the `YYYY/MM/DD.log.php` file, where YYYY is the current
	 * year, MM is the current month, and DD is the current day.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		$filtered_messages = $this->filter($messages);

		if (empty($filtered_messages))
		{
			return;
		}

		// Set the yearly and monthly directory name
		$directory = $this->_directory . date('Y' . DIRECTORY_SEPARATOR . 'm');

		if ( ! is_dir($directory))
		{
			// Create the monthly directory
			mkdir($directory, 02777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($directory, 02777);
		}

		// Set the name of the log file
		$filename = $directory.DIRECTORY_SEPARATOR.date('d').EXT;

		if ( ! file_exists($filename))
		{
			// Create the log file
			file_put_contents($filename, '<?php exit; ?>'.PHP_EOL.PHP_EOL);

			// Allow anyone to write to log files
			chmod($filename, 0666);
		}

		$formatted_messages = array();
		foreach ($filtered_messages as $message)
		{
			$formatted_messages[] = $this->format_message($message);
		}

		file_put_contents($filename, implode(PHP_EOL, $formatted_messages).PHP_EOL, FILE_APPEND);
	}

}
