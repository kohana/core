<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_414 extends Kohana_Http_Exception_414 {

	/**
	 * @var   integer    HTTP 414 Request-URI Too Long
	 */
	protected $_code = 414;

}