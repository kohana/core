<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_408 extends Http_Exception {

	/**
	 * @var   integer    HTTP 408 Request Timeout
	 */
	protected $_code = 408;

}