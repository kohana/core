<?php

namespace Kohana\Core\HTTP;

use Exception;
use Kohana\Core\Kohana\KohanaException;

abstract class HttpException extends KohanaException {

	/**
	 * Creates an HTTP_Exception of the specified type.
	 *
	 * @param   integer $code       the http status code
	 * @param   string  $message    status message, custom content to display with error
	 * @param   array   $variables  translation variables
	 * @return  self
	 */
	public static function factory($code, $message = NULL, array $variables = NULL, Exception $previous = NULL)
	{
		$class = 'HTTP_Exception_'.$code;

		return new $class($message, $variables, $previous);
	}

	/**
	 * @var  int        http status code
	 */
	protected $_code = 0;

	/**
	 * @var  RequestInterface    Request instance that triggered this exception.
	 */
	protected $_request;

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new KohanaException('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string  $message    status message, custom content to display with error
	 * @param   array   $variables  translation variables
	 * @return  void
	 */
	public function __construct($message = NULL, array $variables = NULL, Exception $previous = NULL)
	{
		parent::__construct($message, $variables, $this->_code, $previous);
	}

	/**
	 * Store the Request that triggered this exception.
	 *
	 * @param   RequestInterface   $request  Request object that triggered this exception.
	 * @return  self
	 */
	public function request(RequestInterface $request = NULL)
	{
		if ($request === NULL)
			return $this->_request;

		$this->_request = $request;

		return $this;
	}

	/**
	 * Generate a Response for the current Exception
	 *
	 * @uses   KohanaException::response()
	 * @return ResponseInterface
	 */
	public function get_response()
	{
		return KohanaException::response($this);
	}

}
