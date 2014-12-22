# Kohana PHP Framework - core

| ver   | Stable                                                                                                                       | Develop                                                                                                                        |
|-------|------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------|
| 3.3.x | [![Build Status - 3.3/master](https://travis-ci.org/kohana/core.svg?branch=3.3%2Fmaster)](https://travis-ci.org/kohana/core) | [![Build Status - 3.3/develop](https://travis-ci.org/kohana/core.svg?branch=3.3%2Fdevelop)](https://travis-ci.org/kohana/core) |
| 3.4.x | [![Build Status - 3.4/master](https://travis-ci.org/kohana/core.svg?branch=3.4%2Fmaster)](https://travis-ci.org/kohana/core) | [![Build Status - 3.4/develop](https://travis-ci.org/kohana/core.svg?branch=3.4%2Fdevelop)](https://travis-ci.org/kohana/core) |

This is the core package for the [Kohana](http://kohanaframework.org/) object oriented HMVC framework built using PHP5.
It aims to be swift, secure, and small.

Released under a [BSD license](http://kohanaframework.org/license), Kohana can be used legally for any open source,
commercial, or personal project.

## Documentation and installation

See the [sample application repository](https://github.com/kohana/kohana) for full readme and contributing information.
You will usually add `kohana/core` as a dependency in your own project's composer.json to install and work with this
package.

## Installation for development

To work on this package, you'll want to install it with composer to get the required dependencies. Note that there are
currently circular dependencies between this module and kohana/unittest. These may cause you problems if you are working
on a feature branch, because composer may not be able to figure out which version of kohana core you have.

To work around this, run composer like: `COMPOSER_ROOT_VERSION=3.3.x-dev composer install`. This tells composer that the
current checkout is a 3.3.* development version. Obviously change the argument if your branch is based on a different
version.

After installing the dependencies, you'll need a skeleton Kohana application before you can run the unit tests etc. The
simplest way to do this is to use kohana/koharness to build a bare project in `/tmp/koharness`.

If in doubt, check the install and test steps in the [.travis.yml](.travis.yml) file.
