<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Validation_Exception extends Kohana_Exception {

	/**
	 * @var  object  Validation instance
	 */
	public $array;

	public function __construct(Validation $array, $message = 'Failed to validate array', array $values = NULL, $code = 0)
	{
		$this->array = $array;

		parent::__construct($message, $values, $code);
	}

} // End Kohana_Validation_Exception
