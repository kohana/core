<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_500 extends Http_Exception {

	/**
	 * @var   integer    HTTP 500 Internal Server Error
	 */
	protected $_code = 500;

}