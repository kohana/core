This page wll discuss routing and give **lots of examples** of routes. It seems a lot of new users struggle with routing.

Explain the purpose of routes creating an interface between urls and the controllers.  You can move controllers or change your url scheme and as long as you make the correct routes, everything will still work fine.

Discuss using reverse routing to create links that will be correct even if routes change.

<http://kohanaframework.org/guide/tutorials.urls>

<http://kerkness.ca/wiki/doku.php?id=routing:routing_basics>

<http://kerkness.ca/wiki/doku.php?id=routing:ignoring_overflow_in_a_route>

<http://kerkness.ca/wiki/doku.php?id=routing:building_routes_with_subdirectories>

Testing routes:

<http://kerkness.ca/wiki/doku.php?id=routing:how_to_test_the_routes>

# Routing

Kohana provides a very powerful routing system.  In essence, routes provide an interface between the urls and your controllers and actions.  With the correct routes you could make almost any url scheme correspond to almost any arrangement of controllers.

As mention in the [Request Flow](flow) section, a request is handled by the [Request] class, which will look for a matching [Route] and load the appropriate controller to handle that request.

[!!] It is important to understand that **routes are matched in the order they are added**, and as soon as a URL matches a route, routing is essentially "stopped" and *the remaining routes are never tried*.  Because the default route matches almost anything, including an empty url, new routes must be place before it.

## Creating routes

If you look in `APPPATH/bootstrap.php` you will see the "default" route as follows:

	Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'welcome',
		'action'     => 'index',
	));

So this creates a route with the name `default` that will match urls in the format of `(<controller>(/<action>(/<id>)))`.  

Let's take a closer look at each of the parameters of [Route::set], which are `name`, `uri`, and an optional array `regex`.

### Name

The name of the route must be a **unique** string.  If it is not it will overwrite the older route with the same name. The name is used for creating urls by reverse routing, or checking which route was matched.

### Uri

The uri is a string that represents the format of urls that should be matched.  The tokens surrounded with `<>` are *keys* and anything surrounded with `()` are *optional* parts of the uri. In Kohana routes, any character is allowed and treated literally aside from `()<>`.  The `/` has no meaning besides being a character that must match in the uri.  Usually the `/` is used as a static seperator but as long as the regex makes sense, there are no restrictions to how you can format your routes.

Lets look at the default route again, the uri is `(<controller>(/<action>(/<id>)))`.  We have three keys or params: controller, action, and id.   In this case, the entire uri is optional, so a blank uri would match and the default controller and action (set by defaults(), [covered below](#defaults)) would be assumed resulting in the `Controller_Welcome` class being loaded and the `action_index` method being called to handle the request.

You can use any name you want for your keys, but the following keys have special meaning to the [Request] object, and will influence which controller and action are called:

 * **Directory** - The sub-directory of `classes/controller` to look for the controller (\[covered below]\(#directory))
 * **Controller** - The controller that the request should execute.
 * **Action** - The action method to call.

### Regex

The Kohana route system uses [perl compatible regular expressions](http://perldoc.perl.org/perlre.html) in its matching process.  By default each key (surrounded by `<>`) will match `[^/.,;?\n]++` (or in english: anything that is not a slash, period, comma, semicolon, question mark, or newline).  You can define your own patterns for each key by passing an associative array of keys and patterns as an additional third argument to Route::set.

### Defaults

## Request parameters

$this->request->action

## Where do routes go?

The established convention is to either place your custom routes in the `MODPATH/<module>/init.php` file of your module if the routes belong to a module, or simply insert them into the `APPPATH/bootstrap.php` file **above** the default route if they are specific to the application. Of course, they could also be included from an external file, a database, or even generated dynamically.