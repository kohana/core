<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_406 extends Kohana_Http_Exception_406 {

	/**
	 * @var   integer    HTTP 406 Not Acceptable
	 */
	protected $_code = 406;

}