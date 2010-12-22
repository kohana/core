<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_401 extends Kohana_Http_Exception_401 {

	/**
	 * @var   integer    HTTP 401 Unauthorized
	 */
	protected $_code = 401;

}