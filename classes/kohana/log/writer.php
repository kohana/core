<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Log writer abstract class.
 *
 * @package    Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
abstract class Kohana_Log_Writer {

	/**
	 * Write an array of messages.
	 *
	 * @param   array  messages
	 * @return  void
	 */
	abstract public function write(array $messages);

	/**
	 * Allows the object to have a unique key in associative arrays.
	 *
	 * @return  string
	 */
	final public function __toString()
	{
		return spl_object_hash($this);
	}

} // End Kohana_Log_Writer