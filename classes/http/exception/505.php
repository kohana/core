<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_505 extends Kohana_Http_Exception_505 {

	/**
	 * @var   integer    HTTP 505 HTTP Version Not Supported
	 */
	protected $_code = 505;

}