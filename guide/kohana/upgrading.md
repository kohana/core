# Upgrading from 2.3.x

*This page needs reviewed for accuracy by the development team.*

Most of Kohana v3 works very differently from Kohana 2.3, here's a list of common gotchas and tips for upgrading.

## Naming conventions

The 2.x series differentiated between different 'types' of class (i.e. controller, model etc.) using suffixes.  Folders within model / controller folders didn't have any bearing on the name of the class.

In 3.0 this approach has been scrapped in favour of the Zend framework filesystem conventions, where the name of the class is a path to the class itself, separated by underscores instead of slashes (i.e. `/some/class/file.php` becomes `Some_Class_File`).

See the [conventions documentation](start.conventions) for more information.

## Input Library

The Input Library has been removed from 3.0 in favour of just using `$_GET` and `$_POST`.

### XSS Protection

If you need to XSS clean some user input you can use [Security::xss_clean] to sanitise it, like so:

	$_POST['description'] = security::xss_clean($_POST['description']);

You can also use the [Security::xss_clean] as a filter with the [Validate] library:

	$validation = new Validate($_POST);

	$validate->filter('description', 'Security::xss_clean');

### POST & GET

One of the great features of the Input library was that if you tried to access the value in one of the superglobal arrays and it didn't exist the Input library would return a default value that you could specify i.e.:

	$_GET = array();

	// $id is assigned the value 1
	$id = Input::instance()->get('id', 1);

	$_GET['id'] = 25;

	// $id is assigned the value 25
	$id = Input::instance()->get('id', 1);

In 3.0 you can duplicate this functionality using [Arr::get]:

	$_GET = array();

	// $id is assigned the value 1
	$id = Arr::get($_GET, 'id', 1);

	$_GET['id'] = 42;

	// $id is assigned the value 42
	$id = Arr::get($_GET, 'id', 1);

## ORM Library

There have been quite a few major changes in ORM since 2.3, here's a list of the more common upgrading problems.

### Member variables

All member variables are now prefixed with an underscore (_) and are no longer accessible via `__get()`. Instead you have to call a function with the name of the property, minus the underscore.

For instance, what was once `loaded` in 2.3 is now `_loaded` and can be accessed from outside the class via `$model->loaded()`.

### Relationships

In 2.3 if you wanted to iterate a model's related objects you could do:

	foreach($model->{relation_name} as $relation)

However, in the new system this won't work.   In version 2.3 any queries generated using the Database library were generated in a global scope, meaning that you couldn't try and build two queries simultaneously.  Take for example:

# TODO: NEED A DECENT EXAMPLE!!!!

This query would fail as the second, inner query would 'inherit' the conditions of the first one, thus causing pandemonia.
In v3.0 this has been fixed by creating each query in its own scope, however this also means that some things won't work quite as expected.  Take for example:

	foreach(ORM::factory('user', 3)->where('post_date', '>', time() - (3600 * 24))->posts as $post)
	{
		echo $post->title;
	}

[!!] (See [the Database tutorial](tutorials.databases) for the new query syntax)

In 2.3 you would expect this to return an iterator of all posts by user 3 where `post_date` was some time within the last 24 hours, however instead it'll apply the where condition to the user model and return a `Model_Post` with the joining conditions specified.

To achieve the same effect as in 2.3 you need to rearrange the structure slightly:

	foreach(ORM::factory('user', 3)->posts->where('post_date', '>', time() - (36000 * 24))->find_all() as $post)
	{
		echo $post->title;
	}

This also applies to `has_one` relationships:

	// Incorrect
	$user = ORM::factory('post', 42)->author;
	// Correct
	$user = ORM::factory('post', 42)->author->find();

### Has and belongs to many relationships

In 2.3 you could specify `has_and_belongs_to_many` relationships.  In 3.0 this functionality has been refactored into `has_many` *through*.

In your models you define a `has_many` relationship to the other model but then you add a `'through' => 'table'` attribute, where `'table'` is the name of your through table. For example (in the context of posts<>categories):

	$_has_many = array
	(
		'categories' => 	array
							(
								'model' 	=> 'category', // The foreign model
								'through'	=> 'post_categories' // The joining table
							),
	);

If you've set up kohana to use a table prefix then you don't need to worry about explicitly prefixing the table.

### Foreign keys

If you wanted to override a foreign key in 2.x's ORM you had to specify the relationship it belonged to, and your new foreign key in the member variable `$foreign_keys`.

In 3.0 you now define a `foreign_key` key in the relationship's definition, like so:

	Class Model_Post extends ORM
	{
		$_belongs_to = 	array
						(
							'author' => array
										(
											'model' 		=> 'user',
											'foreign_key' 	=> 'user_id',
										),
						);
	}

In this example we should then have a `user_id` field in our posts table.



In has_many relationships the `far_key` is the field in the through table which links it to the foreign table and the foreign key is the field in the through table which links "this" model's table to the through table.

Consider the following setup, "Posts" have and belong to many "Categories" through `posts_sections`.

| categories | posts_sections 	| posts   |
|------------|------------------|---------|
| id		 | section_id		| id	  |
| name		 | post_id			| title   |
|			 | 					| content |

		Class Model_Post extends ORM
		{
			protected $_has_many = 	array(
										'sections' =>	array(
															'model' 	=> 'category',
															'through'	=> 'posts_sections',
															'far_key'	=> 'section_id',
														),
									);
		}

		Class Model_Category extends ORM
		{
			protected $_has_many = 	array (
										'posts'		=>	array(
															'model'			=> 'post',
															'through'		=> 'posts_sections',
															'foreign_key'	=> 'section_id',
														),
									);
		}


Obviously the aliasing setup here is a little crazy, but it's a good example of how the foreign/far key system works.

### ORM Iterator

It's also worth noting that `ORM_Iterator` has now been refactored into `Database_Result`.

If you need to get an array of ORM objects with their keys as the object's pk, you need to call [Database_Result::as_array], e.g.

		$objects = ORM::factory('user')->find_all()->as_array('id');

Where `id` is the user table's primary key.

## Router Library

In version 2 there was a Router library that handled the main request.  It let you define basic routes in a `config/routes.php` file and it would allow you to use custom regex for the routes, however it was fairly inflexible if you wanted to do something radical.

## Routes

The routing system (now refered to as the request system) is a lot more flexible in 3.0. Routes are now defined in the bootstrap file (`application/bootstrap.php`) and the module init.php (`modules/module_name/init.php`). It's also worth noting that routes are evaluated in the order that they are defined.

Instead of defining an array of routes you now create a new [Route] object for each route. Unlike in the 2.x series there is no need to map one uri to another. Instead you specify a pattern for a uri, use variables to mark the segments (i.e. controller, method, id).

For example, in 2.x these regexes:

	$config['([a-z]+)/?(\d+)/?([a-z]*)'] = '$1/$3/$1';

Would map the uri `controller/id/method` to `controller/method/id`.  In 3.0 you'd use:

	Route::set('reversed','(<controller>(/<id>(/<action>)))')
			->defaults(array('controller' => 'posts', 'action' => 'index'));

[!!] Each uri should have be given a unique name (in this case it's `reversed`), the reasoning behind this is explained in [the url tutorial](tutorials.urls).

Angled brackets denote dynamic sections that should be parsed into variables. Rounded brackets mark an optional section which is not required. If you wanted to only match uris beginning with admin you could use:

	Rouse::set('admin', 'admin(/<controller>(/<id>(/<action>)))');

And if you wanted to force the user to specify a controller:

	Route::set('admin', 'admin/<controller>(/<id>(/<action>))');

Also, Kohana does not use any 'default defaults'.  If you want Kohana to assume your default action is 'index', then you have to tell it so! You can do this via [Route::defaults].  If you need to use custom regex for uri segments then pass an array of `segment => regex` i.e.:

	Route::set('reversed', '(<controller>(/<id>(/<action>)))', array('id' => '[a-z_]+'))
			->defaults(array('controller' => 'posts', 'action' => 'index'))

This would force the `id` value to consist of lowercase alpha characters and underscores.

### Actions

One more thing we need to mention is that methods in a controller that can be accessed via the url are now called "actions", and are prefixed with 'action_'. E.g. in the above example, if the user calls `admin/posts/1/edit` then the action is `edit` but the method called on the controller will be `action_edit`.  See [the url tutorial](tutorials.urls) for more info.

## Sessions

There are no longer any Session::set_flash(), Session::keep_flash() or Session::expire_flash() methods, instead you must use [Session::get_once].

## URL Helper

Only a few things have changed with the url helper - `url::redirect()` has been moved into `$this->request->redirect()` within controllers) and `Request::instance()->redirect()` instead.

`url::current` has now been replaced with `$this->request->uri()`

## Valid / Validation

These two classes have been merged into a single class called `Validate`.

The syntax has also changed a little for validating arrays:

	$validate = new Validate($_POST);

	// Apply a filter to all items in the arrays
	$validate->filter(TRUE, 'trim');

	// To specify rules individually use rule()
	$validate
		->rule('field', 'not_empty')
		->rule('field', 'matches', array('another_field'));

	// To set multiple rules for a field use rules(), passing an array of rules => params as the second argument
	$validate->rules('field', 	array(
									'not_empty' => NULL,
									'matches'	=> array('another_field')
								));

The 'required' rule has also been renamed to 'not_empty' for clarity's sake.

## View Library

There have been a few minor changes to the View library which are worth noting.

In 2.3 views were rendered within the scope of the controller, allowing you to use `$this` as a reference to the controller within the view, this has been changed in 3.0. Views now render in an empty scope. If you need to use `$this` in your view you can bind a reference to it using [View::bind]: `$view->bind('this', $this)`.

It's worth noting, though, that this is *very* bad practice as it couples your view to the controller, preventing reuse.  The recommended way is to pass the required variables to the view like so:

	$view = View::factory('my/view');

	$view->variable = $this->property;

	// OR if you want to chain this

	$view
		->set('variable', $this->property)
		->set('another_variable', 42);

	// NOT Recommended
	$view->bind('this', $this);

Because the view is rendered in an empty scope `Controller::_kohana_load_view` is now redundant.  If you need to modify the view before it's rendered (i.e. to add a generate a site-wide menu) you can use [Controller::after].

	Class Controller_Hello extends Controller_Template
	{
		function after()
		{
			$this->template->menu = '...';

			return parent::after();
		}
	}