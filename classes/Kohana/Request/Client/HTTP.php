<?php

namespace Kohana\Core\Request\Client;

use HttpEncodingException;
use HTTPMalformedHeaderException;
use HttpRequest;
use HttpRequestException;
use Kohana\Core\HTTP\RequestInterface;
use Kohana\Core\Request;
use Request_Exception;
use Kohana\Core\Response;

/**
 * [Request_Client_External] HTTP driver performs external requests using the
 * php-http extension. To use this driver, ensure the following is completed
 * before executing an external request- ideally in the application bootstrap.
 *
 * @example
 *
 *       // In application bootstrap
 *       Request_Client_External::$client = 'Request_Client_HTTP';
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 * @uses       [PECL HTTP](http://php.net/manual/en/book.http.php)
 */
class HTTP extends External {

	/**
	 * Creates a new `Request_Client` object,
	 * allows for dependency injection.
	 *
	 * @param   array    $params Params
	 * @throws  Request_Exception
	 */
	public function __construct(array $params = array())
	{
		// Check that PECL HTTP supports requests
		if ( ! http_support(HTTP_SUPPORT_REQUESTS))
		{
			throw new Request_Exception('Need HTTP request support!');
		}

		// Carry on
		parent::__construct($params);
	}

	/**
	 * @var     array     curl options
	 * @link    http://www.php.net/manual/function.curl-setopt
	 */
	protected $_options = array();

	/**
	 * Sends the HTTP message [Request] to a remote server and processes
	 * the response.
	 *
	 * @param   Request   $request  request to send
	 * @param   Response  $request  response to send
	 * @return  Response
	 */
	public function _send_message(Request $request, Response $response)
	{
		$http_method_mapping = array(
			RequestInterface::GET     => HTTPRequest::METH_GET,
			RequestInterface::HEAD    => HTTPRequest::METH_HEAD,
			RequestInterface::POST    => HTTPRequest::METH_POST,
			RequestInterface::PUT     => HTTPRequest::METH_PUT,
			RequestInterface::DELETE  => HTTPRequest::METH_DELETE,
			RequestInterface::OPTIONS => HTTPRequest::METH_OPTIONS,
			RequestInterface::TRACE   => HTTPRequest::METH_TRACE,
			RequestInterface::CONNECT => HTTPRequest::METH_CONNECT,
		);

		// Create an http request object
		$http_request = new HTTPRequest($request->uri(), $http_method_mapping[$request->method()]);

		if ($this->_options)
		{
			// Set custom options
			$http_request->setOptions($this->_options);
		}

		// Set headers
		$http_request->setHeaders($request->headers()->getArrayCopy());

		// Set cookies
		$http_request->setCookies($request->cookie());

		// Set query data (?foo=bar&bar=foo)
		$http_request->setQueryData($request->query());

		// Set the body
		if ($request->method() == RequestInterface::PUT)
		{
			$http_request->addPutData($request->body());
		}
		else
		{
			$http_request->setBody($request->body());
		}

		try
		{
			$http_request->send();
		}
		catch (HTTPRequestException $e)
		{
			throw new Request_Exception($e->getMessage());
		}
		catch (HTTPMalformedHeaderException $e)
		{
			throw new Request_Exception($e->getMessage());
		}
		catch (HTTPEncodingException $e)
		{
			throw new Request_Exception($e->getMessage());
		}

		// Build the response
		$response->status($http_request->getResponseCode())
			->headers($http_request->getResponseHeader())
			->cookie($http_request->getResponseCookies())
			->body($http_request->getResponseBody());

		return $response;
	}

}
