# Helpers

[Helpers](../kohana/helpers) are similiar to libraries but differ in that they use static methods and do not have to be instantiated in order to be used.

You can make your own helpers by simply creating a class and putting it in the `classes` directory.

## Adding your own

To add your own helpers you only need to create a class and place it in the `APPPATH/classes/` directory.

In the below example we'll create a `Foo` class with a `bar()` method which will be located at `APPPATH/classes/foo.php`.

    class Foo {

        public static function bar()
        {
            // Magic happens here
        }
    }

To access the method we just call it like so:

    Foo::bar();


[!!] Helper files follow the same [naming conventions](conventions#class-names-and-file-location) as library files in that underscores in class names equate to directory separators. For example, if you created the `Foo_Bar` helper class, Kohana will expect to find it located at `APPPATH/classes/foo/bar.php`.

## Transparent extension

To build on or modify already existing Kohana helpers, you just need to extend them when creating your helper class. Kohana's cascading filesystem allows you to do so via its [transparent class extension](extension).

For example, you want to add a new method named `bar()` that extends the [URL] helper. Simply declare it like so:

    class URL extends Kohana_URL {

        public static function bar()
        {
            // Do your magic
        }
    }

Now, when you want to use the new method you call it like you would an existing helper method:

    $foo = URL::bar();
