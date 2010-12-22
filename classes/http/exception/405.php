<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_405 extends Kohana_Http_Exception_405 {

	/**
	 * @var   integer    HTTP 405 Method Not Allowed
	 */
	protected $_code = 405;

}