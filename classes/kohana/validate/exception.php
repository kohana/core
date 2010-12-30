<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Validate_Exception extends Kohana_Exception {

	/**
	 * @var  object  Validate instance
	 */
	public $array;

	/**
	 * @param  Validate  Validate object
	 * @param  string    error message
	 * @param  array     translation variables
	 * @param  int       the exception code
	 */
	public function __construct(Validate $array, $message = 'Failed to validate array', array $values = NULL, $code = 0)
	{
		$this->array = $array;

		parent::__construct($message, $values, $code);
	}

} // End Kohana_Validate_Exception
