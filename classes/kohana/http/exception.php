<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Http_Exception extends Kohana_Exception {

	/**
	 * @var     int      http status code
	 */
	protected $_code = 0;

	/**
	 * @var     string   file of view to use for http exeception
	 */
	protected $_http_view = 'kohana/http/error';

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string   status message, custom content to display with error
	 * @param   array    translation variables
	 * @param   integer  the http status code
	 * @return  void
	 */
	public function __construct($message = NULL, array $variables = NULL, $code = 0)
	{
		if ($code == 0)
		{
			$code = $this->_code;
		}

		if ( ! isset(Response::$messages[$code]))
			throw new Kohana_Exception('Unrecognized HTTP status code: :code . Only valid HTTP status codes are acceptable, see RFC 2616.', array(':code' => $code));

		parent::__construct($message, $variables, $code);
	}

} // End Http_Exception
