---
title: Autoloading
---

PHPStan needs a working autoloader to access reflection of the analysed classes. It uses Composer autoloader in the project by looking at `vendor/autoload.php` from the current working directory. Use the `autoload`/`autoload-dev` sections in `composer.json` to configure the autoloader.

If PHPStan complains about some non-existent classes and you're sure the classes exist in the codebase and you don't want to use Composer autoloader, you can specify directories to scan and concrete files to include using `autoload_directories` and `autoload_files` parameters in the [configuration file](/config-reference).

`autoload_directories` is for discovering classes, interfaces, and traits, `autoload_files` is used for loading function definitions.

```yaml
parameters:
	autoload_directories:
		- build
	autoload_files:
		- generated/routes/GeneratedRouteList.php
```

Relative paths in the `autoload_directories` and `autoload_files` keys are resolved based on the directory of the config file is in.

Constants
-------------

PHPStan doesn't automatically know about global constants in the analysed project. You need to provide them by defining all of them in a single place and let PHPStan autoload this file.

Create a file `constants.php` with this content:

```php
define('MY_CONSTANT', 1);
define('MY_OTHER_CONSTANT', 2);
```

And put it into the `autoload_files` key in the [configuration file](/config-reference):

```yaml
parameters:
	autoload_files:
		- constants.php
```