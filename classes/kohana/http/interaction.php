<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The HTTP Interaction interface providing the core HTTP methods that
 * should be implemented by any HTTP request or response class.
 *
 * @package    Kohana
 * @category   Http
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
interface Kohana_Http_Interaction {

	/**
	 * Gets or sets the HTTP protocol. The standard protocol to use
	 * is `HTTP/1.1`.
	 *
	 * @param   string   protocol to set to the request/response
	 * @return  self|string
	 */
	public function protocol($protocol = NULL);

	/**
	 * Gets or sets HTTP headers to the request or response. All headers
	 * are included immediately after the HTTP protocol definition during
	 * transmission. This method provides a simple array or key/value
	 * interface to the headers.
	 *
	 * @param   string|array   key or array of key/value pairs to set
	 * @param   string         value to set to the supplied key
	 * @return  array|string|self
	 */
	public function headers($key = NULL, $value = NULL);

	/**
	 * Gets or sets the HTTP body to the request or response. The body is
	 * included after the header, separated by a single empty new line.
	 *
	 * @param   string         content to set to the object
	 * @return  string|void
	 */
	public function body($content = NULL);

	/**
	 * Renders the Http_Interaction to a string, producing
	 * 
	 *  - Protocol
	 *  - Headers
	 *  - Body
	 *
	 * @return  string
	 */
	public function render();
}