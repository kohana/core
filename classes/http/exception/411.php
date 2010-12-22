<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_411 extends Kohana_Http_Exception_411 {

	/**
	 * @var   integer    HTTP 411 Length Required
	 */
	protected $_code = 411;

}