# Loading Classes

Kohana takes advantage of PHP [autoloading](http://php.net/manual/language.oop5.autoload.php). This removes the need to call [include](http://php.net/include) or [require](http://php.net/require) before using a class. For instance, when you want to use the [Cookie::set] method, you just call:

    Cookie::set('mycookie', 'any string value');

Or to load an [Encrypt] instance, just call [Encrypt::instance]:

    $encrypt = Encrypt::instance();

Classes are loaded via the [Kohana::auto_load] method, which makes a simple conversion from class name to file name:

1. Classes are placed in the `classes/` directory of the [filesystem](about.filesystem)
2. Any underscore characters are converted to slashes
2. The filename is lowercase

When calling a class that has not been loaded (eg: `Session_Cookie`), Kohana will search the filesystem using [Kohana::find_file] for a file named `classes/session/cookie.php`.

## Custom Autoloaders

The default autoloader is enabled in `application/bootstrap.php` using [spl_autoload_register](http://php.net/spl_autoload_register):

    spl_autoload_register(array('Kohana', 'auto_load'));

This allows [Kohana::auto_load] to attempt to load any class that does not yet exist when the class is first used.

# Transparent Class Extension {#class-extension}

The [cascading filesystem](about.filesystem) allows transparent class extension. For instance, the class [Cookie] is defined in `SYSPATH/classes/cookie.php` as:

    class Cookie extends Kohana_Cookie {}

The default Kohana classes, and many extensions, use this definition so that almost all classes can be extended. You extend any class transparently, by defining your own class in `APPPATH/classes/cookie.php` to add your own methods.

[!!] You should **never** modify any of the files that are distributed with Kohana. Always make modifications to classes using extensions to prevent upgrade issues.

For instance, if you wanted to create method that sets encrypted cookies using the [Encrypt] class:

    <?php defined('SYSPATH') or die('No direct script access.');

    class Cookie extends Kohana_Cookie {

        /**
         * @var  mixed  default encryption instance
         */
        public static $encryption = 'default';

        /**
         * Sets an encrypted cookie.
         *
         * @uses  Cookie::set
         * @uses  Encrypt::encode
         */
         public static function encrypt($name, $value, $expiration = NULL)
         {
             $value = Encrypt::instance(Cookie::$encrpytion)->encode((string) $value);

             parent::set($name, $value, $expiration);
         }

         /**
          * Gets an encrypted cookie.
          *
          * @uses  Cookie::get
          * @uses  Encrypt::decode
          */
          public static function decrypt($name, $default = NULL)
          {
              if ($value = parent::get($name, NULL))
              {
                  $value = Encrypt::instance(Cookie::$encryption)->decode($value);
              }

              return isset($value) ? $value : $default;
          }

    } // End Cookie

Now calling `Cookie::encrypt('secret', $data)` will create an encrypted cookie which we can decrypt with `$data = Cookie::decrypt('secret')`.

## Multiple Levels of Extension {#multiple-extensions}

If you are extending a Kohana class in a module, you should maintain transparent extensions. Instead of making the [Cookie] extension extend Kohana, you can create `MODPATH/mymod/encrypted/cookie.php`:

    class Encrypted_Cookie extends Kohana_Cookie {

        // Use the same encrypt() and decrypt() methods as above

    }

And create `MODPATH/mymod/cookie.php`:

    class Cookie extends Encrypted_Cookie {}

This will still allow users to add their own extension to [Cookie] with your extensions intact. However, the next extension of [Cookie] will have to extend `Encrypted_Cookie` instead of `Kohana_Cookie`.