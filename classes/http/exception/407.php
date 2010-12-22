<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_407 extends Kohana_Http_Exception_407 {

	/**
	 * @var   integer    HTTP 407 Proxy Authentication Required
	 */
	protected $_code = 407;

}