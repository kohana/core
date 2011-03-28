<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_503 extends Http_Exception {

	/**
	 * @var   integer    HTTP 503 Service Unavailable
	 */
	protected $_code = 503;

}