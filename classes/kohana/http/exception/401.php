<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_401 extends Http_Exception {

	/**
	 * @var   integer    HTTP 401 Unauthorized
	 */
	protected $_code = 401;

}