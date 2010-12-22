<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_500 extends Kohana_Http_Exception_500 {

	/**
	 * @var   integer    HTTP 500 Internal Server Error
	 */
	protected $_code = 500;

}