<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_412 extends Kohana_Http_Exception_412 {

	/**
	 * @var   integer    HTTP 412 Precondition Failed
	 */
	protected $_code = 412;

}