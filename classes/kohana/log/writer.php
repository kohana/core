<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Log writer abstract class. All [Kohana_Log] writers must extend this class.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
abstract class Kohana_Log_Writer {

	/**
	 * Write an array of messages.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array  messages
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

} // End Kohana_Log_Writer
