<?php defined('SYSPATH') or die('No direct script access.');
/**
 * A HTTP Request specific interface that adds the methods required
 * by HTTP requests. Over and above [Kohana_Http_Interaction], this
 * interface provides method, uri, get and post methods.
 *
 * @package    Kohana
 * @category   Http
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
interface Kohana_Http_Request extends Http_Interaction {

	/**
	 * Gets or sets the Http method. Usually GET, POST, PUT or DELETE in
	 * traditional CRUD applications.
	 *
	 * @param   string   method to use for this request
	 * @return  self|string
	 */
	public function method($method = NULL);

	/**
	 * Gets the URI of this request, optionally allows setting
	 * of [Route] specific parameters during the URI generation.
	 * If no parameters are passed, the request will use the
	 * default values defined in the Route.
	 *
	 * @param   array    optional parameters to include in uri generation
	 * @return  string
	 */
	public function uri(array $params = NULL);

	/**
	 * Gets or sets HTTP query string.
	 *
	 * @param   string|array key or key value pairs to set
	 * @param   string   value to set to a key
	 * @return  self|mixed
	 */
	public function query($key = NULL, $value = NULL);

	/**
	 * Gets or sets HTTP POST parameters to the request.
	 *
	 * @param   string|array key or key value pairs to set
	 * @param   string   value to set to a key
	 * @return  self|mixed
	 */
	public function post($key = NULL, $value = NULL);

}