<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_502 extends Kohana_Http_Exception_502 {

	/**
	 * @var   integer    HTTP 502 Bad Gateway
	 */
	protected $_code = 502;

}