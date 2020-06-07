---
title: Discovering Symbols
showBanner: false
---

<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">

Discovering Symbols is a new feature in PHPStan 0.12.26. Make sure to upgrade to the latest version and take advantage of the latest features!

Looking for the deprecated [autoloading](/user-guide/autoloading) instead?

</div>

PHPStan needs to be able to locate symbols (classes, functions, constants) used in the analysed codebase. By default, it looks for them in these two places:

* Analysed paths (files and directories) passed as [command line arguments](/user-guide/command-line-usage) and in the [configuration file](/config-reference#analysed-files).
* Composer dependencies of the analysed project

This covers most common needs.

However, there are some advanced scenarios that might require some additional configuration.

Third party code outside of Composer dependencies
---------------------------

If your project uses some code that isn't part of your Composer dependencies, but you don't wish to analyse it, you can take advantage of `scanFiles` and `scanDirectories` config options:

```yaml
parameters:
    scanFiles:
      - Foo.class.php
    scanDirectories:
      - classes
```

Relative paths in the `scanFiles` and `scanDirectories` keys are resolved based on the directory of the config file is in.

Global constants
---------------------------

Global constants used in the analysed code need to be defined in bootstrap files.

Create a file a that looks like this:

```php
<?php

define('MY_CONSTANT', 1);
```

And add it to your [configuration file](/config-reference):

```yaml
parameters:
    bootstrapFiles:
        - constants.php
```

Please note that bootstrap files will actually be executed by the PHP runtime.

Class aliases
---------------------------

This is similar to global constants above. Class aliases used in the analysed code need to be defined in bootstrap files.

Create a file a that looks like this:

```php
<?php

class_alias(\Foo::class, 'Bar');
```

And add it to your [configuration file](/config-reference):

```yaml
parameters:
    bootstrapFiles:
        - classAliases.php
```

Please note that bootstrap files will actually be executed by the PHP runtime.

Custom autoloader
---------------------------

If you're using some other autoloader than the one in Composer, PHPStan can take advantage of it to discover files with autoloaded classes.

You can register the custom autoloader in two ways:

1) By passing the PHP file that registers the autoloader as [`--autoload-file|-a` on the command line](/user-guide/command-line-usage#--autoload-file|-a).
2) By using the `bootstrapFiles` option:

```yaml
parameters:
    bootstrapFiles:
        - my_autoloader.php
```

Please note that the file with the autoloader will actually be executed by the PHP runtime.
