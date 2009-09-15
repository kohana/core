<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Controller class for RESTful controller mapping.
 *
 * @package    Controller
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Kohana_Controller_REST extends Controller {

	protected $_action_map = array
	(
		'GET'    => 'index',
		'PUT'    => 'create',
		'POST'   => 'update',
		'DELETE' => 'delete',
	);

	protected $_action_requested = '';

	public function before()
	{
		$this->_action_requested = $this->request->action;

		if ( ! isset($this->_action_map[Request::$method]))
		{
			$this->request->action = 'invalid';
		}
		else
		{
			$this->request->action = strtolower(Request::$method);
		}
	}

	public function action_invalid()
	{
		// Send the "Method Not Allowed" response
		$this->request->status = 405;
		$this->request->headers['Allow'] = implode(', ', array_keys($this->_action_map));
	}

} // End REST
