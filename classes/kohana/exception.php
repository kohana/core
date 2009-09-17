<?php defined('SYSPATH') or die('No direct access');
/**
 * Kohana exception class. Converts exceptions into HTML messages.
 *
 * @package    Exception
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Exception extends Exception {

	/**
	 * Creates a new translated exception.
	 *
	 * @param   string   error message
	 * @param   array    translation variables
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		// Set the message
		$message = __($message, $variables);

		// Pass the message to the parent
		parent::__construct($message, $code);
	}

	/**
	 * Magic object-to-string method.
	 *
	 * @uses    Kohana::exception_text
	 * @return  string
	 */
	public function __toString()
	{
		return Kohana::exception_text($this);
	}

} // End Kohana_Exception
