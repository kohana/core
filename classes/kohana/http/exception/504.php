<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Http_Exception_504 extends Http_Exception {

	/**
	 * @var   integer    HTTP 504 Gateway Timeout
	 */
	protected $_code = 504;

}