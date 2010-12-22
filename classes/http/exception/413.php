<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_413 extends Kohana_Http_Exception_413 {

	/**
	 * @var   integer    HTTP 413 Request Entity Too Large
	 */
	protected $_code = 413;

}