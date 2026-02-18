---
title: "classConstant.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class Config
{
	public const VERSION = '1.0';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Config;

$version = Config::VERSION;
```

## Why is it reported?

A constant is being accessed on a class that is marked as `@internal`. Internal classes are not meant to be used outside the package or namespace where they are defined. Accessing constants on internal classes creates a dependency on implementation details that may change without notice.

In the example above, `Config::VERSION` accesses a constant on the internal `Config` class from outside its namespace.

## How to fix it

Use a public (non-internal) API to access the information:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\Config;
+use SomeLibrary\Application;

-$version = Config::VERSION;
+$version = Application::getVersion();
```

If no public API exists, consider requesting one from the library maintainers. Relying on internal classes is fragile and may break on library updates.
