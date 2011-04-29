<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */

class Kohana_Request_Client_HTTP extends Request_Client_External {

	/**
	 * Sends the HTTP message [Request] to a remote server and processes
	 * the response.
	 *
	 * @param   Request   request to send
	 * @return  Response
	 * @uses    [PECL HTTP](http://php.net/manual/en/book.http.php)
	 */
	public function _send_message(Request $request)
	{
		$http_method_mapping = array(
			HTTP_Request::GET     => HTTPRequest::METH_GET,
			HTTP_Request::HEAD    => HTTPRequest::METH_HEAD,
			HTTP_Request::POST    => HTTPRequest::METH_POST,
			HTTP_Request::PUT     => HTTPRequest::METH_PUT,
			HTTP_Request::DELETE  => HTTPRequest::METH_DELETE,
			HTTP_Request::OPTIONS => HTTPRequest::METH_OPTIONS,
			HTTP_Request::TRACE   => HTTPRequest::METH_TRACE,
			HTTP_Request::CONNECT => HTTPRequest::METH_CONNECT,
		);

		// Create an http request object
		$http_request = new HTTPRequest($request->uri(), $http_method_mapping[$request->method()]);

		// Set custom options
		$http_request->setOptions($this->_options);

		// Set headers
		$http_request->setHeaders($request->headers()->getArrayCopy());

		// Set cookies
		$http_request->setCookies($request->cookie());

		// Set the body
		$http_request->setBody($request->body());

		try
		{
			$http_request->send();
		}
		catch (HTTPRequestException $e)
		{
			throw new Kohana_Request_Exception($e->getMessage());
		}
		catch (HTTPMalformedHeaderException $e)
		{
			throw new Kohana_Request_Exception($e->getMessage());
		}
		catch (HTTPEncodingException $e)
		{
			throw new Kohana_Request_Exception($e->getMessage());
		}

		// Create the response
		$response = $request->create_response();

		// Build the response
		$response->status($http_request->getResponseCode())
			->headers($http_request->getResponseHeader())
			->cookie($http_request->getResponseCookies())
			->body($http_request->getResponseBody());

		return $response;
	}

} // End Kohana_Request_Client_HTTP