<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_409 extends Kohana_Http_Exception_409 {

	/**
	 * @var   integer    HTTP 409 Conflict
	 */
	protected $_code = 409;

}