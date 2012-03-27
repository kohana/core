# Migrating from 3.2.x

## HVMC Isolation

HVMC Sub-request isolation has been improved to prevent exceptions leaking from this inner to the outer request. If you we're previous catching any exceptions from sub-requests, you should now be checking the [Response] object returned from [Request::execute].

## HTTP Exceptions

The use of HTTP Exceptions is now encouraged over manually setting the [Response] status to, for example, '404'. This allows for easier custom error pages (detailed below);

The full list of supported codes can be seen in the SYSPATH/classes/http/exception/ folder.

Syntax:

    throw HTTP_Exception::factory($code, $message, array $variables, Exception $previous);

Examples:

    // Page Not Found
    throw HTTP_Exception::factory(404, 'The requested URL :uri was not found on this server.', array(
            ':uri' => $this->request->uri(),
        ));

    // Unauthorized / Login Requied
    throw HTTP_Exception::factory(401)->authenticate('Basic realm="MySite"');

    // Forbidden / Permission Deined
    throw HTTP_Exception::factory(403);

## Redirects (HTTP 300, 301, 302, 303, 307)

Redirects are no longer issued against the [Request] object. The new syntax from inside a controler is:

    $this->redirect('http://www.google.com', 302);

or from outside a controller:

    HTTP::redirect('http://www.google.com', 302);

## Custom Error Pages (HTTP 500, 404, 403, 401 etc)

Custom error pages are now easier than ever to implement, thanks to some of the changes brought about by the HVMC and Redirect changes above.

See [Custom Error Pages](tutorials/error-pages) for more details.

## Browser cache checking (ETag's)

The Response::check_cache method has moved to [HTTP::check_cache], with an alias at [Controller::check_cache]. Previously, this method would be used from a controller like this:

    $this->response->check_cache(sha1('my content'), Request $this->request);

Now, there are two options for using the method:

    $this->check_cache(sha1('my content'));

which is an alias for:

    HTTP::check_cache($this->request, $this->response, sha1('my content'));
    
## PSR-0 support (file/class naming conventions)
With the introduction of [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) support, the autoloading of classes is case sensitive. Now, the file (and folder) 
names must match the class name exactly.

Examples:

    Kohana_Core

would be located in
    
    classes/Kohana/Core.php

and

    Kohana_HTTP_Header

would be located in 

    classes/Kohana/HTTP/Header.php

This also affects dynamically named classes such as drivers and orms, so for example in the database config using `'mysql'` as the type instead of `'MySQL'` would throw a class not found error.