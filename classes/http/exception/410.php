<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_410 extends Kohana_Http_Exception_410 {

	/**
	 * @var   integer    HTTP 410 Gone
	 */
	protected $_code = 410;

}