<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_404 extends Kohana_Http_Exception_404 {

	/**
	 * @var   integer    HTTP 404 Not Found
	 */
	protected $_code = 404;

}