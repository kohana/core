<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_404 extends Http_Exception {

	/**
	 * @var   integer    HTTP 404 Not Found
	 */
	protected $_code = 404;

}