# Creating a template driven site

Templates allow you to separate the design and layout (view) from the logic and code (control) of a webpage. It provides a structure for consistent HTML presentation across multiple pages and can be made of many components. But let's start with a sample application that uses a single template file.

## Template view

The default template controller (`Controller_Template`) looks in the `views` folder for a `template.php` file. For our example we'll be placing the demo template file under a subfolder.

Create a blank document at `/application/views/template/demo.php` and enter the following code:

    <!doctype html>
    <html lang="<?php echo I18n::$lang ?>">
      <head>
        <meta charset="utf-8">
        <title><?php echo $title ?></title>
        <?php

        foreach ($styles as $file => $type)
        {
        	echo HTML::style($file, array('media' => $type));
        }

		foreach ($scripts as $file)
		{
			echo HTML::script($file);
		}

		?>
      </head>
      <body>
        <?php echo $content ?>
      </body>
    </html>

This basic template file consists of (and expects) the following:

- `$title` (string) - Title of the page
- `$scripts` (array) - JavaScript files required by the page
- `$styles` (array) - CSS stylesheets required by the template
- `$content` (string) - Content output of the page

The page also has a language attribute defined by `I18n::$lang`, where the default is set to `en-us`.


## Template controller

While you can work directly with `Controller_Template`, extending it better allows you to customise values and output based on various requests and requirements.

Create a new template controller at `/application/classes/controller/demo.php` with the following code:


    <?php defined('SYSPATH') OR die('No direct script access.');

    class Controller_Demo extends Controller_Template {

    	// Template layout to use, found in the views folder
        public $template = 'template/demo';

        /**
         * The before() method is called before your controller action.
         * In our template controller we override this method so that we can
         * set up default values. These variables are then available to our
         * controllers if they need to be modified.
         */
        public function before()
        {
            parent::before();

            if ($this->auto_render)
            {
                // Initialize empty values
                $this->template->title   = '';
                $this->template->content = '';

                $this->template->styles = array();
                $this->template->scripts = array();
            }
        }

        /**
         * The after() method is called after your controller action.
         * In our template controller we override this method so that we can
         * make any last minute modifications to the template before anything
         * is rendered. This also adds the CSS and JavaScript that will be
         * needed by pages using this template.
         */
        public function after()
        {
            if ($this->auto_render)
            {
                $styles = array(
                    'media/css/print.css' => 'print',
                    'media/css/style.css' => 'screen',
                );

                $scripts = array(
                    'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
                );

                $this->template->styles = Arr::merge($this->template->styles, $styles);
                $this->template->scripts = Arr::merge($this->template->scripts, $scripts);
            }

	        parent::after();
        }
    }


## Page controller

In order to make use of our template view and template controller, we'll need to create a page controller to bring the two together and serve our pages.

Create a new page controller at `/application/classes/controller/page.php` with the following:

    <?php defined('SYSPATH') OR die('No direct script access.');

    class Controller_Page extends Controller_Demo {

        public function action_home()
        {
            $this->template->title = __('Welcome to Marshmallow Dreams');
            $this->template->content = View::factory('page/home');
        }

        public function action_contact()
        {
            $this->template->title = __('Contact us at Marshmallow Dreams');
            $this->template->content = View::factory('page/contact');
        }
    }

Note the call to two new views, `page/home` and `page/contact`.

Your homepage view is expected at `/application/views/page/home.php`. Create a blank file with the following placeholder content:

    <h1>Welcome home, Starshine</h1>

    <p>This is your homepage.</p>

Your contact view will be located at `/application/views/page/contact.php` and may contain something like:

    <h1>Contact Us</h1>

    <p>Please hire only left-handed pigeons when mailing letters.</p>

## Controller hierarchy

In building our basic template driven site we've also organised the controllers to the following heirarchy:

- `Controller_Template` extends `Controller`
- `Controller_Demo` extends `Controller_Template`
- `Controller_Page` extends `Controller_Demo`

## Congratulations!

You've now created your first set of pages using Kohana templates, views and controllers.

Adapted from: [Building a Template Driven Web Site - The Unofficial Kohana 3 Wiki](http://kerkness.ca/kowiki/doku.php?id=template-site:create_the_template)