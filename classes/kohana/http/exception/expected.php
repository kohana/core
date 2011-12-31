<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_HTTP_Exception_Expected extends HTTP_Exception {

	/**
	 * @var  Response   Response Object
	 */
	protected $_response;

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string  $message    status message, custom content to display with error
	 * @param   array   $variables  translation variables
	 * @return  void
	 */
	public function __construct($message = NULL, array $variables = NULL, Exception $previous = NULL)
	{
		parent::__construct($message, $variables, $previous);

		// Prepare our response object and set the correct status code.
		$this->_response = Response::factory()
			->status($this->_code);
	}

	/**
	 * Gets and sets headers to the [Response].
	 * 
	 * @see     [Response::headers]
	 * @param   mixed   $key
	 * @param   string  $value
	 * @return  mixed
	 */
	public function headers($key = NULL, $value = NULL)
	{
		$result = $this->_response->headers($key, $value);

		if ( ! $result instanceof Response)
			return $result;

		return $this;
	}

	/**
	 * Generate a Response for the current Exception
	 * 
	 * @uses   Kohana_Exception::response()
	 * @return Response
	 */
	public function get_response()
	{
		return $this->_response;
	}

} // End Kohana_HTTP_Exception