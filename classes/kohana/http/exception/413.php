<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Http_Exception_413 extends Http_Exception {

	/**
	 * @var   integer    HTTP 413 Request Entity Too Large
	 */
	protected $_code = 413;

} // End Http_Exception_413
