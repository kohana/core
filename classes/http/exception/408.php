<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_408 extends Kohana_Http_Exception_408 {

	/**
	 * @var   integer    HTTP 408 Request Timeout
	 */
	protected $_code = 408;

}