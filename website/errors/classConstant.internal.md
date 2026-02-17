---
title: "classConstant.internal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

class Config
{
	/** @internal */
	public const DEBUG_MODE = true;

	public const VERSION = '1.0';
}
```

```php
<?php declare(strict_types = 1);

// In your code:

use SomeLibrary\Config;

echo Config::DEBUG_MODE;
```

## Why is it reported?

The class constant is marked as `@internal` and is being accessed from outside the package where it is defined. Internal constants are not part of the package's public API and may change or be removed without notice.

## How to fix it

Use a public (non-internal) constant or API instead:

```diff-php
 <?php declare(strict_types = 1);

 use SomeLibrary\Config;

-echo Config::DEBUG_MODE;
+echo Config::VERSION;
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
