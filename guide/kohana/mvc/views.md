# Views

Views are files that contain the display information for your application. This is most commonly HTML, CSS and Javascript but can be anything you require such as XML or JSON for AJAX output. The purpose of views is to keep this information separate from your application logic for easy reusability and cleaner code.

Views themselves can contain code used for displaying the data you pass into them. For example, looping through an array of product information and display each one on a new table row. Views are still PHP files so you can use any code you normally would.  However, you should try to keep your views as "dumb" as possible and retreive all data you need in your controllers, then pass it to the view.

# Creating View Files

View files are stored in the `views` directory of the [filesystem](files). You can also create sub-directories within the `views` directory to organize your files. All of the following examples are reasonable view files:

    APPPATH/views/home.php
    APPPATH/views/pages/about.php
    APPPATH/views/products/details.php
    MODPATH/error/views/errors/404.php
    MODPATH/common/views/template.php

## Loading Views

[View] objects will typically be created inside a [Controller](mvc/controllers) using the [View::factory] method. Typically the view is then assigned as the [Request::$response] property or to another view.

    public function action_about()
    {
        $this->response->body(View::factory('pages/about'));
    }

When a view is assigned as the [Response::body], as in the example above, it will automatically be rendered when necessary. To get the rendered result of a view you can call the [View::render] method or just type cast it to a string. When a view is rendered, the view file is loaded and HTML is generated.

    public function action_index()
    {
        $view = View::factory('pages/about');

        // Render the view
        $about_page = $view->render();

        // Or just type cast it to a string
        $about_page = (string) $view;

        $this->response->body($about_page);
    }

## Variables in Views

Once view has been loaded, variables can be assigned to it using the [View::set] and [View::bind] methods.

    public function action_roadtrip()
    {
        $view = View::factory('user/roadtrip')
            ->set('places', array('Rome', 'Paris', 'London', 'New York', 'Tokyo'));
            ->bind('user', $this->user);

        // The view will have $places and $user variables
        $this->response->body($view);
    }

[!!] The only difference between `set()` and `bind()` is that `bind()` assigns the variable by reference. If you `bind()` a variable before it has been defined, the variable will be created with a value of `NULL`.  

You can also assign variables directly to the View object.  This is identical to calling `set()`;

	public function action_roadtrip()
	{
		$view = View::factory('user/roadtrip');
            
		$view->places = array('Rome', 'Paris', 'London', 'New York', 'Tokyo');
        $view->user = $this->user;

        // The view will have $places and $user variables
        $this->response->body($view);
	}

### Dealing With Global-like Variables

An application may have several view files that need access to the same variable - for example, to display a
page title in both the `<head>` and `<body>` of your content. You should track these variables and set them
on the views that require them prior to rendering.

For example, you could do this with a controller property:

    class Controller_Home extends Controller {
        protected $page_title;

        public function action_index()
        {
            $this->page_title = 'Home';
            $content = View::factory('pages/home', array('page_title' => $this->page_title));
            $this->render_template($content);
        }

        protected function render_template(View $content)
        {
            $template = View::factory('template', array('page_title' => $this->page_title));
            $sidebar  = View::factory('template/sidebar', array('page_title' => $this->page_title));
            $template->set('sidebar', $sidebar);
            $template->set('content', $content);
            $this->response->body($template->render());
        }
    }

If you have a small number of variables - such as page title and username - that may be required in
lots of views it may be sensible to use a factory class or dependency injection container to store
these variables and provide them to views as required.

[!!] The [View::set_global] and [View::bind_global] methods are retained for backwards compatibility,
     but are deprecated. We strongly advise against using them, as this creates global state with
     potential to introduce hidden dependencies, variable naming conflicts and hard-to-debug problems.

## Views Within Views

If you want to include another view within a view, there are two choices. By calling [View::factory] you can sandbox the included view. This means that you will have to provide all of the variables to the view using [View::set] or [View::bind]:
	
	// In your view file:
	
    // Only the $user variable will be available in "views/user/login.php"
    <?php echo View::factory('user/login')->bind('user', $user) ?>

The other option is to include the view directly, which makes all of the current variables available to the included view:

	// In your view file:
	
    // Any variable defined in this view will be included in "views/message.php"
    <?php include Kohana::find_file('views', 'user/login') ?>

You can also assign a variable of your parent view to be the child view from within your controller.  For example:

	// In your controller:

	public function action_index()
	{
		$view = View::factory('common/template');
		
		$view->title = "Some title";
		$view->body = View::factory('pages/foobar');
	}
	
	// In views/common/template.php:
	
	<html>
	<head>
		<title><?php echo $title></title>
	</head>
	
	<body>
		<?php echo $body ?>
	</body>
	</html>

Of course, you can also load an entire [Request] within a view:

    <?php echo Request::factory('user/login')->execute() ?>

This is an example of \[HMVC], which makes it possible to create and read calls to other URLs within your application.