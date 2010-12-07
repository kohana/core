<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Controller class for RESTful controller mapping. Supports GET, PUT,
 * POST, and DELETE. By default, these methods will be mapped to these actions:
 *
 * GET
 * :  Mapped to the "index" action, lists all objects
 *
 * POST
 * :  Mapped to the "create" action, creates a new object
 *
 * PUT
 * :  Mapped to the "update" action, update an existing object
 *
 * DELETE
 * :  Mapped to the "delete" action, delete an existing object
 *
 * Additional methods can be supported by adding the method and action to
 * the `$_action_map` property.
 *
 * [!!] Using this class within a website will require heavy modification,
 * due to most web browsers only supporting the GET and POST methods.
 * Generally, this class should only be used for web services and APIs.
 *
 * @package    Kohana
 * @category   Controller
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Controller_REST extends Controller {

	/**
	 * @var  array  REST types
	 */
	protected $_action_map = array
	(
		'GET'    => 'index',
		'PUT'    => 'update',
		'POST'   => 'create',
		'DELETE' => 'delete',
	);

	/**
	 * @var  string  requested action
	 */
	protected $_action_requested = '';

	/**
	 * Checks the requested method against the available methods. If the method
	 * is supported, sets the request action from the map. If not supported,
	 * the "invalid" action will be called.
	 */
	public function before()
	{
		$this->_action_requested = $this->request->action;

		if ( ! isset($this->_action_map[Request::$method]))
		{
			$this->request->action = 'invalid';
		}
		else
		{
			$this->request->action = $this->_action_map[Request::$method];
		}

		return parent::before();
	}

	/**
	 * Sends a 405 "Method Not Allowed" response and a list of allowed actions.
	 */
	public function action_invalid()
	{
		// Send the "Method Not Allowed" response
		$this->request->status = 405;
		$this->request->headers['Allow'] = implode(', ', array_keys($this->_action_map));
	}

} // End REST
