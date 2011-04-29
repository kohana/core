<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */

class Kohana_Request_Client_Stream extends Request_Client_External {

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
		// Calculate stream mode
		$mode = ($request->method() === HTTP_Request::GET) ? 'r' : 'r+';

		// Process cookies
		if ($cookies = $request->cookie())
		{
			$request->headers('cookie', http_build_query($cookies, NULL, '; '));
		}

		// Get the message body
		$body = $request->body();

		// Set the content length
		$request->headers('content-length', strlen($body));

		// Create the context
		$options = array(
			$request->protocol() => array(
				'method'     => $request->method(),
				'header'     => (string) $request->headers(),
				'content'    => $body,
				'user-agent' => 'Kohana Framework '.Kohana::VERSION.' ('.Kohana::CODENAME.')'
			)
		);

		// Create the context stream
		$context = stream_context_create($options);

		stream_context_set_option($context, $this->_options);

		$stream = fopen($request->uri(), $mode, FALSE, $context);

		$meta_data = stream_get_meta_data($stream);

		// Get the HTTP response code
		$http_response = array_shift($meta_data['wrapper_data']);

		if (preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $http_response, $matches) !== FALSE)
		{
			$protocol = $matches[1][0];
			$status   = (int) $matches[2][0];
		}
		else
		{
			$protocol = NULL;
			$status   = NULL;
		}

		// Create a response
		$response = $request->create_response();
		$response_header = $response->headers();

		// Process headers
		array_map(array($response_header, 'parse_header_string'), NULL, $meta_data['wrapper_data']);

		$response->status($status)
			->protocol($protocol)
			->headers(Request_Client_External::$_processed_headers)
			->body(stream_get_contents($stream));

		// Close the stream after use
		fclose($stream);

		return $response;
	}

} // End Kohana_Request_Client_Stream