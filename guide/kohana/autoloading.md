# Loading Classes

Kohana takes advantage of PHP [autoloading](http://php.net/manual/language.oop5.autoload.php). This removes the need to call [include](http://php.net/include) or [require](http://php.net/require) before using a class. When you use a class Kohana will find and include the class file for you. For instance, when you want to use the [Cookie::set] method, you simply call:

    Cookie::set('mycookie', 'any string value');

Or to load an [Encrypt] instance, just call [Encrypt::instance]:

    $encrypt = Encrypt::instance();

Classes are loaded via the [Kohana::auto_load] method, which makes a simple conversion from class name to file name:

1. Classes are placed in the `classes/` directory of the [filesystem](files)
2. Any underscore characters in the class name are converted to slashes
2. The filename is lowercase

When calling a class that has not been loaded (eg: `Session_Cookie`), Kohana will search the filesystem using [Kohana::find_file] for a file named `classes/session/cookie.php`.

If your classes do not follow this convention, they cannot be autoloaded by Kohana.  You will have to manually included your files, or add your own [autoload function.](http://us3.php.net/manual/en/function.spl-autoload-register.php)

## Custom Autoloaders

Kohana's default autoloader is enabled in `application/bootstrap.php` using [spl_autoload_register](http://php.net/spl_autoload_register):

    spl_autoload_register(array('Kohana', 'auto_load'));

This allows [Kohana::auto_load] to attempt to load any class that does not yet exist when the class is first used.