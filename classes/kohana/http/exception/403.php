<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_403 extends Http_Exception {

	/**
	 * @var   integer    HTTP 403 Forbidden
	 */
	protected $_code = 403;

}