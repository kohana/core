# Moving application and system out of the docroot

Kohana follows a [front controller] pattern, which means that all requests are sent to `index.php`. This allows a very clean [filesystem](about.filesystem) design. In `index.php`, there are some very basic configuration options available. You can change the `$application`, `$modules`, and `$system` paths and set the error reporting level.

The `$application` variable lets you set the directory that contains your application files. By default, this is `application`. The `$modules` variable lets you set the directory that contains module files. The `$system` variable lets you set the directory that contains the default Kohana files.

You can move these three directories anywhere. For instance, if your directories are set up like this:

    www/
        index.php
        application/
        modules/
        system/

You could move the directories out of the web root:

    application/
    modules/
    system/
    www/
        index.php

Then you would change the settings in `index.php` to be:

    $application = '../application';
    $modules     = '../modules';
    $system      = '../system';

Now none of the directories can be accessed by the web server. It is not necessary to make this change, but does make it possible to share the directories with multiple applications, among other things.

[!!] There is a security check at the top of every Kohana file to prevent it from being accessed without using the front controller. However, it is more secure to move the application, modules, and system directories to a location that cannot be accessed via the web. 