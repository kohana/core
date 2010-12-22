<?php defined('SYSPATH') or die('No direct script access.');

class Http_Exception_416 extends Kohana_Http_Exception_416 {

	/**
	 * @var   integer    HTTP 416 Request Range Not Satisfiable
	 */
	protected $_code = 416;

}