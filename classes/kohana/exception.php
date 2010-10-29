<?php defined('SYSPATH') or die('No direct access');
/**
 * Kohana exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Exception extends Exception {

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string     error message
	 * @param   array      translation variables
	 * @param   integer    the exception code
	 * @param   Exception  previous exception
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0, Exception $previous = NULL)
	{
		// Set the message
		$message = __($message, $variables);

		// Pass the message to the parent
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Magic object-to-string method.
	 *
	 *     echo $exception;
	 *
	 * @uses    Kohana::exception_text
	 * @return  string
	 */
	public function __toString()
	{
		return Kohana::exception_text($this);
	}

} // End Kohana_Exception
