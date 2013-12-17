# Conventions

## Class Names and File Location

Class names in Kohana follow a strict convention to facilitate [autoloading](autoloading). Class names should have uppercase first letters with underscores to separate words. Underscores are significant as they directly reflect the file location in the filesystem.

The following conventions apply:

1. CamelCased class names should be used when it is undesirable to create a new directory level.
2. All class file names and directory names must match the case of the class as per [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
3. All classes should be in the `classes` directory. This may be at any level in the [cascading filesystem](files).

### Examples  {#class-name-examples}

Remember that in a class, an underscore means a new directory. Consider the following examples:

Class Name            | File Path
----------------------|-------------------------------
Controller_Template   | classes/Controller/Template.php
Model_User            | classes/Model/User.php
Model_BlogPost        | classes/Model/BlogPost.php
Database              | classes/Database.php
Database_Query        | classes/Database/Query.php
Form                  | classes/Form.php
