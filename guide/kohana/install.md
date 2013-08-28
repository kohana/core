# Requirements

[!!] Before continuing, make sure you have a web server (like Apache) configured with the following requirements.

 - PHP 5.3.3 or newer.
 - [Iconv Extension](http://php.net/iconv)
 - [Character Type (CTYPE) Extension](http://php.net/ctype)

# Download

You can get the latest **stable** release on the [Kohana website](http://kohanaframework.org/). This will give you a fully functional application with an `application`, `modules`, and `system` directory.

[!!] You can find information about the file structure on the [Cascading Filesystem](files) page.

Once downloaded, you should extract the Kohana application to a directory where the web server can access it. Going forward, we are going to assume you've extracted the application to a `kohana` directory such that `http://localhost/kohana/index.php` is pointing to the `index.php` file in the Kohana release.

# Configure

Before the application can be run, you will need to make a few changes to the `application/bootstrap.php` file. This file is the first one to be included by `index.php` and sets up most of the global options for the application. Open `application/bootstrap.php` and make the following changes:

 - Set the default [timezone](http://php.net/timezones) for your application.
~~~
// Example of changing timezone to Sao Paulo, Brazil
date_default_timezone_set('America/Sao_Paulo');
~~~
 - Set the `base_url` in the [Kohana::init] call to reflect the location of the kohana folder on your server relative to the document root.
~~~
/**
 * Example of kohana's installation at /var/www/kohana and
 * Apache's DocumentRoot configured to /var/www
 */
Kohana::init(array(
	'base_url' => '/kohana/',
));
~~~

 - Make sure the `application/cache` and `application/logs` directories are writable by the web server.
~~~
sudo chmod -R a+rwx application/cache
sudo chmod -R a+rwx application/logs
~~~

 - Define a salt for the `Cookie` class.
~~~
Cookie::$salt = [really-long-cookie-salt-here]
~~~

[!!] Make sure to use a unique salt for your application and never to share it. Take a look at the [Cookies](cookies) page for more information on how cookies work in Kohana. If you do not define a `Cookie::$salt` value, Kohana will throw an exception when it encounters any cookie on your domain.

 - Test your installation by opening [http://localhost/kohana](http://localhost/kohana).

You should see the installation page. If it reports any errors, you will need to correct them before continuing.

![Install Page](install.png "Example of install page")

Once your install page reports that your environment is set up correctly you need to either rename or delete `install.php`. Kohana is now installed and you should see the output of the welcome controller:

![Welcome Page](welcome.png "Example of welcome page")

## Installing Kohana From GitHub

The [source code](http://github.com/kohana/kohana) for Kohana is hosted with [GitHub](http://github.com). To install Kohana using the github source code first you need to install [git](http://git-scm.com/). Visit [http://help.github.com](http://help.github.com) for details on how to install git on your platform.

[!!] For more information on installing Kohana using git, see the [Working with Git](tutorials/git) tutorial.
