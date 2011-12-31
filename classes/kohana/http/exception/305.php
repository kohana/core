<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_HTTP_Exception_305 extends HTTP_Exception_Expected {

	/**
	 * @var   integer    HTTP 305 Use Proxy
	 */
	protected $_code = 305;

}