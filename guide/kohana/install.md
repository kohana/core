# Installation

1. Download the latest **stable** release from the [Kohana website](http://kohanaframework.org/).
2. Unzip the downloaded package to create a `kohana` directory.
3. Upload the contents of this folder to your webserver.
4. Open `application/bootstrap.php` and make the following changes:
	- Set the default [timezone](http://php.net/timezones) for your application.
	- Set the `base_url` in the [Kohana::init] call to reflect the location of the kohana folder on your server.
6. Make sure the `application/cache` and `application/logs` directories are writable by the web server.
7. Test your installation by opening the URL you set as the `base_url` in your favorite browser.

[!!] Depending on your platform, the installation's subdirs may have lost their permissions thanks to zip extraction. Chmod them all to 755 by running `find . -type d -exec chmod 0755 {} \;` from the root of your Kohana installation.

You should see the installation page. If it reports any errors, you will need to correct them before continuing.

![Install Page](install.png "Example of install page")

Once your install page reports that your environment is set up correctly you need to either rename or delete `install.php` in the root directory. You should then see the Kohana welcome page:

![Welcome Page](welcome.png "Example of welcome page")

## Setting up a production environment

There are a few things you'll want to do with your application before moving into production.

1. See the [Bootstrap page](bootstrap) in the docs.
   This covers most of the global settings that would change between environments.
   As a general rule, you should enable caching and disable profiling ([Kohana::init] settings) for production sites.
   [Route::cache] can also help if you have a lot of routes.
2. Catch all exceptions in `application/bootstrap.php`, so that sensitive data is cannot be leaked by stack traces.
   See the example below which was taken from Shadowhand's [wingsc.com source](http://github.com/shadowhand/wingsc).
3. Turn on APC or some kind of opcode caching.
   This is the single easiest performance boost you can make to PHP itself. The more complex your application, the bigger the benefit of using opcode caching.

		/**
		 * Set the environment string by the domain (defaults to Kohana::DEVELOPMENT).
		 */
		Kohana::$environment = ($_SERVER['SERVER_NAME'] !== 'localhost') ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
		/**
		 * Initialise Kohana based on environment
		 */
		Kohana::init(array(
			'base_url'   => '/',
			'index_file' => FALSE,
			'profile'    => Kohana::$environment !== Kohana::PRODUCTION,
			'caching'    => Kohana::$environment === Kohana::PRODUCTION,
		));

		/**
		 * Execute the main request using PATH_INFO. If no URI source is specified,
		 * the URI will be automatically detected.
		 */
		$request = Request::instance($_SERVER['PATH_INFO']);

		try
		{
			// Attempt to execute the response
			$request->execute();
		}
		catch (Exception $e)
		{
			if (Kohana::$environment === Kohana::DEVELOPMENT)
			{
				// Just re-throw the exception
				throw $e;
			}

			// Log the error
			Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));

			// Create a 404 response
			$request->status = 404;
			$request->response = View::factory('template')
			  ->set('title', '404')
			  ->set('content', View::factory('errors/404'));
		}

		if ($request->send_headers()->response)
		{
			// Get the total memory and execution time
			$total = array(
			  '{memory_usage}' => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2).'KB',
			  '{execution_time}' => number_format(microtime(TRUE) - KOHANA_START_TIME, 5).' seconds');

			// Insert the totals into the response
			$request->response = str_replace(array_keys($total), $total, $request->response);
		}


		/**
		 * Display the request response.
		 */
		echo $request->response;

## Installing Kohana 3.0 From GitHub

The [source](http://github.com/kohana/kohana) code for Kohana 3.0 is hosted with [GitHub](http://github.com).  To install Kohana using the github source code first you need to install git.

### Quick Start

Visit [http://help.github.com](http://help.github.com) for details on how to install git on your platform then follow these steps:

    git clone git://github.com/kohana/kohana.git
    cd kohana/
    git submodule init
    git submodule update


### Creating a New Application

[!!] The following examples assume that your web server is already set up, and you are going to create a new application at <http://localhost/gitorial/>.

Using your console, change to the empty directory `gitorial` and run `git init`. This will create the bare structure for a new git repository.

Next, we will create a [submodule](http://www.kernel.org/pub/software/scm/git/docs/git-submodule.html) for the `system` directory. Go to <http://github.com/kohana/core> and copy the "Clone URL":

![Github Clone URL](http://img.skitch.com/20091019-rud5mmqbf776jwua6hx9nm1n.png)

Now use the URL to create the submodule for `system`:

    git submodule add git://github.com/kohana/core.git system

[!!] This will create a link to the current development version of the next stable release. The development version should almost always be safe to use, have the same API as the current stable download with bugfixes applied.

Now add whatever submodules you need. For example, if you need the [Database] module:

    git submodule add git://github.com/kohana/database.git modules/database

After submodules are added, they must be initialized:

    git submodule init

Now that the submodules are added, you can commit them:

    git commit -m 'Added initial submodules'

Next, create the application directory structure. This is the bare minimum required:

    mkdir -p application/classes/{controller,model}
    mkdir -p application/{config,views}
    mkdir -m 0777 -p application/{cache,logs}

If you run `find application` you should see this:

    application
    application/cache
    application/config
    application/classes
    application/classes/controller
    application/classes/model
    application/logs
    application/views

We don't want git to track log or cache files, so add a `.gitignore` file to each of the directories. This will ignore all non-hidden files:

    echo '[^.]*' > application/{logs,cache}/.gitignore

[!!] Git ignores empty directories, so adding a `.gitignore` file also makes sure that git will track the directory, but not the files within it.

Now we need the `index.php` and `bootstrap.php` files:

    wget http://github.com/kohana/kohana/raw/master/index.php
    wget http://github.com/kohana/kohana/raw/master/application/bootstrap.php -O application/bootstrap.php

Commit these changes too:

    git add application
    git commit -m 'Added initial directory structure'

That's all there is to it. You now have an application that is using Git for versioning.

### Adding Submodules
To add a new submodule complete the following steps:

1. run the following code - git submodule add repository path for each new submodule e.g.:

        git submodule add git://github.com/shadowhand/sprig.git modules/sprig

2. then init and update the submodules:

        git submodule init
        git submodule update

### Updating Submodules

At some point you will probably also want to upgrade your submodules. To update all of your submodules to the latest `HEAD` version:

    git submodule foreach 'git checkout master && git pull origin master'

To update a single submodule, for example, `system`:

    cd system
    git checkout master
    git pull origin master
    cd ..
    git add system
    git commit -m 'Updated system to latest version'

If you want to update a single submodule to a specific commit:

    cd modules/database
    git pull origin master
    git checkout fbfdea919028b951c23c3d99d2bc1f5bbeda0c0b
    cd ../..
    git add database
    git commit -m 'Updated database module'

Note that you can also check out the commit at a tagged official release point, for example:

    git checkout 3.0.6

Simply run `git tag` without arguments to get a list of all tags.

### Removing Submodules
To remove a submodule that is no longer needed complete the following steps:

1. open .gitmodules and remove the reference to the to submodule
    It will look something like this:

        [submodule "modules/auth"]
        path = modules/auth
        url = git://github.com/kohana/auth.git

2. open .git/config and remove the reference to the to submodule\\

        [submodule "modules/auth"]
        url = git://github.com/kohana/auth.git

3. run git rm --cached path/to/submodule, e.g.

        git rm --cached modules/auth

**Note:** Do not put a trailing slash at the end of path. If you put a trailing slash at the end of the command, it will fail.