<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract controller class. Controllers should only be created using a [Request].
 *
 * Controllers methods will be automatically called in the following order by
 * the request:
 *
 *     $controller = new Controller_Foo($request);
 *     $controller->before();
 *     $controller->action_bar();
 *     $controller->after();
 *
 * The controller action should add the output it creates to
 * `$this->response->body($output)`, typically in the form of a [View], during the
 * "action" part of execution.
 *
 * @package    Kohana
 * @category   Controller
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Kohana_Controller {

	/**
	 * @var  Request  Request that created the controller
	 */
	public $request;

	/**
	 * @var  Response The response that will be returned from controller
	 */
	public $response;

	/**
	 * Creates a new controller instance. Each controller must be constructed
	 * with the request object that created it.
	 *
	 * @param   Request   $request  Request that created the controller
	 * @param   Response  $response The request's response
	 * @return  void
	 */
	public function __construct(Request $request, Response $response)
	{
		// Assign the request to the controller
		$this->request = $request;

		// Assign a response to the controller
		$this->response = $response;
	}

	/**
	 * Executes the given action and calls the [Controller::before] and [Controller::after] methods.
	 * 
	 * Can also be used to catch exceptions from actions in a single place.
	 * 
	 * 1. Before the controller action is called, the [Controller::before] method
	 * will be called.
	 * 2. Next the controller action will be called.
	 * 3. After the controller action is called, the [Controller::after] method
	 * will be called.
	 * 
	 * @throws  HTTP_Exception_404
	 * @return  Response
	 */
	public function execute()
	{
		// Execute the "before action" method
		$this->before();

		// Determine the action to use
		$action = 'action_'.$this->request->action();

		// If the action doesn't exist, it's a 404
		if ( ! method_exists($this, $action))
		{
			throw new HTTP_Exception_404(
				'The requested URL :uri was not found on this server.',
				array(':uri' => $this->request->uri())
			);
		}

		// Execute the action itself
		$this->{$action}();

		// Execute the "after action" method
		$this->after();

		// Return the response
		return $this->response;
	}

	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return  void
	 */
	public function before()
	{
		// Nothing by default
	}

	/**
	 * Automatically executed after the controller action. Can be used to apply
	 * transformation to the response, add extra output, and execute
	 * other custom code.
	 * 
	 * @return  void
	 */
	public function after()
	{
		// Nothing by default
	}

	/**
	 * Marks up a Response with the approperiate HTTP headers and status code
	 * to redirect the user-agent.
	 * 
	 * [!!] This does not `exit()`, any code after this call (and in the `after()` method) will continue to run unless you `return;`
	 * 
	 *     $this->redirect('account/login', 303);
	 *     return;
	 * 
	 * @param  string    $uri       URI to redirect to
	 * @param  int       $code      Status code (eg 301, 302, 303, 307)
	 * @return Response
	 */
	protected function redirect($uri, $code = 302)
	{
		return HTTP::redirect($this->request, $this->response, $uri, $code);
	}
} // End Controller