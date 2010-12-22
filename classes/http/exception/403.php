<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_403 extends Kohana_Http_Exception_403 {

	/**
	 * @var   integer    HTTP 403 Forbidden
	 */
	protected $_code = 403;

}