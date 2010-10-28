<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File log writer. Writes out messages and stores them in a YYYY/MM directory.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Log_File extends Kohana_Log_Writer {

	// Directory to place log files in
	protected $_directory;

	/**
	 * Creates a new file logger. Checks that the directory exists and
	 * is writable.
	 *
	 *     $writer = new Kohana_Log_File($directory);
	 *
	 * @param   string  log directory
	 * @return  void
	 */
	public function __construct($directory)
	{
		if ( ! is_dir($directory) OR ! is_writable($directory))
		{
			throw new Kohana_Exception('Directory :dir must be writable',
				array(':dir' => Kohana::debug_path($directory)));
		}

		// Determine the directory path
		$this->_directory = realpath($directory).DIRECTORY_SEPARATOR;
	}

	/**
	 * Writes each of the messages into the log file. The log file will be
	 * appended to the `YYYY/MM/DD.log.php` file, where YYYY is the current
	 * year, MM is the current month, and DD is the current day.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		// Set the yearly directory name
		$directory = $this->_directory.date('Y').DIRECTORY_SEPARATOR;

		if ( ! is_dir($directory))
		{
			// Create the yearly directory
			mkdir($directory, 0777);

			// Set permissions (must be manually set to fix umask issues)
			chmod($directory, 0777);
		}

		// Add the month to the directory
		$directory .= date('m').DIRECTORY_SEPARATOR;

		if ( ! is_dir($directory))
		{
			// Create the yearly directory
			mkdir($directory, 0777);

			// Set permissions (must be manually set to fix umask issues)
			chmod($directory, 0777);
		}

		// Set the name of the log file
		$filename = $directory.date('d').EXT;

		if ( ! file_exists($filename))
		{
			// Create the log file
			file_put_contents($filename, Kohana::FILE_SECURITY.' ?>'.PHP_EOL);

			// Allow anyone to write to log files
			chmod($filename, 0666);
		}

		// Set the log line format
		$format = 'time --- type: body';

		foreach ($messages as $message)
		{
			// Write each message into the log file
			file_put_contents($filename, PHP_EOL.strtr($format, $message), FILE_APPEND);
		}
	}

} // End Kohana_Log_File