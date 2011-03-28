<?php defined('SYSPATH') or die('No direct script access.');
/**
 * A HTTP Reponse specific interface that adds the methods required
 * by HTTP responses. Over and above [Kohana_Http_Interaction], this
 * interface provides status.
 *
 * @package    Kohana
 * @category   Http
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaphp.com/license
 */
interface Kohana_Http_Response extends Http_Interaction {

	/**
	 * Sets or gets the HTTP status from this response.
	 *
	 *      // Set the HTTP status to 404 Not Found
	 *      $response = Response::factory()
	 *              ->status(404);
	 *
	 *      // Get the current status
	 *      $status = $response->status();
	 *
	 * @param   integer  $code  Status to set to this response
	 * @return  mixed
	 */
	public function status($code = NULL);

}