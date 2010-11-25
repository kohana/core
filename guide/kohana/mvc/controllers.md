# Controllers

A Controller is a class file that stands in between the models and the views in an application. It passes information on to the model when data needs to be changed and it requests information from the model when data needs to be loaded. Controllers then pass on the information of the model to the views where the final output can be rendered for the users.  Controllers essentially control the flow of the application.

Controllers are called by the [Request::execute()] function based on the [Route] that the url matched.  Be sure to read the [routing](routing) page to understand how to use routes to map urls to your controllers.

## Creating Controllers

In order to function, a controller must do the following:

* Reside in `classes/controller` (or a sub-directory)
* Filename must be lowercase, e.g. `articles.php`
* The class name must map to the filename (with `/` replaced with `_`) and each word is capitalized
* Must have the Controller class as a (grand)parent

Some examples of controller names and file locations:

	// classes/controller/foobar.php
	class Controller_Foobar extends Controller {
	
	// classes/controller/admin.php
	class Controller_Admin extends Controller {

Controllers can be in sub-folders:

	// classes/controller/baz/bar.php
	class Controller_Baz_Bar extends Controller {
	
	// classes/controller/product/category.php
	class Controller_Product_Category extends Controller {
	
[!!] Note that controllers in sub-folders can not be called by the default route, you will need to define a route that has a [directory](routing#directory) param or sets a default value for directory.

Controllers can extend other controllers.

	// classes/controller/users.php
	class Controller_Users extends Controller_Template
	
	// classes/controller/api.php
	class Controller_Api extends Controller_REST
	
[!!] [Controller_Template] and [Controller_REST] are some example controllers provided in Kohana.

You can also have a controller extend another controller to share common things, such as requiring you to be logged in to use all of those controllers.

	// classes/controller/admin.php
	class Controller_Admin extends Controller {
		// This controller would have a before() that checks if the user is logged in
	
	// classes/controller/admin/plugins.php
	class Controller_Admin_Plugins extends Controller_Admin {
		// Because this controller extends Controller_Admin, it would have the same logged in check
		
## $this->request

Every controller has the `$this->request` property which is the [Request] object that called the controller.  You can use this to get information about the current request, as well as set the response via `$this->request->response`.

Here is a partial list of the properties and methods available to `$this->request`.  These can also be accessed via `Request::instance()`, but `$this->request` is provided as a shortcut.  See the [Request] class for more information on any of these. 

Property/method | What it does
--- | ---
[$this->request->route](../api/Request#property:route) | The [Route] that matched the current request url
[$this->request->directory](../api/Request#property:directory), <br /> [$this->request->controller](../api/Request#property:controller), <br /> [$this->request->action](../api/Request#property:action) | The directory, controller and action that matched for the current route
[$this->request->param()](../api/Request#param) | Any other params defined in your route
[$this->request->response](../api/Request#property:response) | The content to return for this request
[$this->request->status](../api/Request#property:status) | The HTTP status for the request (200, 404, 500, etc.)
[$this->request->headers](../api/Request#property:headers) | The HTTP headers to return with the response
[$this->request->redirect()](../api/Request#redirect) | Redirect the request to a different url


## Actions

You create actions for your controller by defining a public function with an `action_` prefix.  Any method that is not declared as `public` and prefixed with `action_` can NOT be called via routing.

An action method will decide what should be done based on the current request, it *controls* the application.  Did the user want to save a blog post?  Did they provide the necesarry fields?   Do they have permission to da that?  The controller will call other classes, including models, to accomplish this.  Every action should set `$this->request->response` to the [view file](mvc/views) to be sent to the browser, unless it [redirected](../api/Request#redirect) or otherwise ended the script earlier.

A very basic action method that simply loads a [view](mvc/views) file.

	public function action_hello()
	{
		$this->request->response = View::factory('hello/world'); // This will load views/hello/world.php
	}

### Parameters

Parameters can be accessed in two ways.  The first is by calling `$this->request->param('name')` where `name` is the name defined in the route.

	// Assuming Route::set('example','<controller>(/<action>(/<id>(/<new>)))');
	
	public function action_foobar()
	{
		$id = $this->request->param('id');
		$new = $this->request->param('new');

If that parameter is not set it will be returned as NULL.  You can provide a second parameter to set a default value if that param is not set.

	public function action_foobar()
	{
		// $id will be false if it was not supplied in the url
		$id = $this->request->param('user',FALSE);

The second way you can access route parameters is from the actions function definition.  Any extra keys in your route (keys besides `<directory>`, `<controller>`, and `<action>`) are passed as parameters to your action *in the order they appear in the route*.  

	// Assuming Route::set('example','<controller>(/<action>(/<id>(/<new>)))');
	
	public function action_foobar($id, $new)
	{

Note that the names do not actually matter, *only the order*.  You could name the parameters anything you want in both the route and the function definition, they don't even need to match.  The following code is identical in function to the previous example.

	// Assuming Route::set('example','<controller>(/<action>(/<num>(/<word>)))');
	
	public function action_foobar($foo, $bar)
	{

You can provide default values in the same way you do for any php function.

	public function action_foobar($id = 0, $new = NULL)
	{

You can use whichever method you prefer.  Using function params is quick and easy and saves on `$this->request->param()` calls, but keep in mind that if your routes ever change it could change the paramater order and break things.  Therefore, it is recommended you use `$this->request->param()`.  For example, assuming the following route

	Route::set('example','<controller>(/<action>(/<id>(/<new>)))');
	
If you called "example/foobar/4/bobcat" you could access the parameters by either:

	public function action_foobar($id, $new) 
	{
	
	// OR
	
	public function action_foobar()
	{
		$id = $this->request->param('id');
		$new = $this->request->param('new');

Then, let's say sometime in the future you change your url schemes and your routes.  The new route is:

	// Note that id and new are switched
	Route::set('example','<controller>(/<action>(/<new>(/<id>)))');

Because the `<new>` and `<id>` keys are in a different order, you will need to fix your function definition to be `action_foobar($new, $id)` whereas the function that used `$this->request->param()` calls would continue to function as desired.
	
### Examples

TODO: some examples of actions

## Before and after

You can use the `before()` and `after()` functions to have code executed before or after the action is executed. For example, you could check if the user is logged in, set a template view, loading a required file, etc.

For example, if you look in `Controller_Template` you can see that in the be

You can check what action has been requested (via `$this->request->action`) and do something based on that, such as requiring the user to be logged in to use a controller, unless they are using the login action.

	// Checking auth/login in before, and redirecting if necessary:

	Controller_Admin extends Controller {

		public function before()
		{
			// If this user doesn't have the admin role, and is not trying to login, redirect to login
			if ( ! Auth::instance()->logged_in('admin') AND $this->request->action !== 'login')
			{
				$this->request->redirect('admin/login');
			}
		}
		
		public function action_login() {
			...

### Custom __construct() function

In general, you should not have to change the `__construct()` function, as anything you need for all actions can be done in `before()`.  If you need to change the controller constructor, you must preserve the parameters or PHP will complain.  This is so the Request object that called the controller is available.  *Again, in most cases you should probably be using `before()`, and not changing the constructor*, but if you really, *really* need to it should look like this:

	// You should almost never need to do this, use before() instead!

	// Be sure Kohana_Request is in the params
	public function __construct(Kohana_Request $request)
	{
		// You must call parent::__construct at some point in your function
		parent::__construct($request);
		
		// Do whatever else you want
	}

## Extending other controllers

TODO: More description and examples of extending other controllers, multiple extension, etc.