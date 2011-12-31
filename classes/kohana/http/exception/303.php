<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_HTTP_Exception_303 extends HTTP_Exception_Expected {

	/**
	 * @var   integer    HTTP 303 See Other
	 */
	protected $_code = 303;

}