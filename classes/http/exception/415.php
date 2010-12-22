<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_415 extends Kohana_Http_Exception_415 {

	/**
	 * @var   integer    HTTP 415 Unsupported Media Type
	 */
	protected $_code = 415;

}