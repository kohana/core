<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Validate_Exception extends Kohana_Exception {

	/**
	 * @var  object  Validate instance
	 */
	public $array;

	public function __construct(Validate $array, $message = 'Failed to validate array', array $values = NULL, $code = 0)
	{
		$this->array = $array;

		parent::__construct($message, $values, $code);
	}

} // End Kohana_Validate_Exception
