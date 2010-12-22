<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_503 extends Kohana_Http_Exception_503 {

	/**
	 * @var   integer    HTTP 503 Service Unavailable
	 */
	protected $_code = 503;

}