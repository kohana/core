<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */

class Kohana_Request_Client_Curl extends Request_Client_External {

	/**
	 * @var     array     curl options
	 * @see     [http://www.php.net/manual/en/function.curl-setopt.php]
	 */
	protected $_options = array();

	/**
	 * Creates a new `Request_Client` object,
	 * allows for dependency injection.
	 *
	 * @param   array    $params Params
	 */
	public function __construct(array $params = array())
	{
		parent::__construct($params);

		$this->_options[CURLOPT_RETURNTRANSFER] = TRUE;
		$this->_options[CURLOPT_HEADER]         = FALSE;
	}

	/**
	 * Sends the HTTP message [Request] to a remote server and processes
	 * the response.
	 *
	 * @param   Request   request to send
	 * @return  Response
	 * @uses    [PHP cURL](http://php.net/manual/en/book.curl.php)
	 */
	public function _send_message(Request $request)
	{
		// Response headers
		$response_headers = array();

		// Set the request method
		$options[CURLOPT_CUSTOMREQUEST] = $request->method();

		// Set the request body. This is perfectly legal in CURL even
		// if using a request other than POST. PUT does support this method
		// and DOES NOT require writing data to disk before putting it, if
		// reading the PHP docs you may have got that impression. SdF
		$options[CURLOPT_POSTFIELDS] = $request->body();

		// Process headers
		if ($headers = $request->headers())
		{
			$http_headers = array();

			foreach ($headers as $key => $value)
			{
				$http_headers[] = $key.': '.$value;
			}

			$options[CURLOPT_HTTPHEADER] = $http_headers;
		}

		// Process cookies
		if ($cookies = $request->cookie())
		{
			$options[CURLOPT_COOKIE] = http_build_query($cookies, NULL, '; ');
		}

		// Create response
		$response = $request->create_response();
		$response_header = $response->headers();

		// Implement the default header parsing
		$options[CURLOPT_HEADERFUNCTION] = array($response_header, 'parse_header_string');

		// Apply any additional options set to 
		$options += $this->_options;

		// Open a new remote connection
		$curl = curl_init($request->uri());

		// Set connection options
		if ( ! curl_setopt_array($curl, $options))
		{
			throw new Kohana_Request_Exception('Failed to set CURL options, check CURL documentation: :url',
				array(':url' => 'http://php.net/curl_setopt_array'));
		}

		// Get the response body
		$body = curl_exec($curl);

		// Get the response information
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($body === FALSE)
		{
			$error = curl_error($curl);
		}

		// Close the connection
		curl_close($curl);

		if (isset($error))
		{
			throw new Kohana_Request_Exception('Error fetching remote :url [ status :code ] :error',
				array(':url' => $request->url(), ':code' => $code, ':error' => $error));
		}

		$response->status($code)
			->body($body);

		return $response;
	}

	/**
	 * Set and get options for this request.
	 *
	 * @param   mixed    $key    Option name, or array of options
	 * @param   mixed    $value  Option value
	 * @return  mixed
	 * @return  Request_Client_External
	 */
	public function options($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_options;

		if (is_array($key))
		{
			$this->_options = $key;
		}
		elseif ( ! $value)
		{
			return Arr::get($this->_options, $key);
		}
		else
		{
			$this->_options[$key] = $value;
		}

		return $this;
	}

} // End Kohana_Request_Client_Curl