<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_HTTP_Exception_307 extends HTTP_Exception_Expected {

	/**
	 * @var   integer    HTTP 307 Temporary Redirect
	 */
	protected $_code = 307;

}