<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_501 extends Kohana_Http_Exception_501 {

	/**
	 * @var   integer    HTTP 501 Not Implemented
	 */
	protected $_code = 501;

}