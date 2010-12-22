<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_504 extends Kohana_Http_Exception_504 {

	/**
	 * @var   integer    HTTP 504 Gateway Timeout
	 */
	protected $_code = 504;

}