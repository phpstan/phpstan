---
title: "staticMethod.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewFactory instead */
class OldFactory
{
	public static function create(): object
	{
		return new \stdClass();
	}
}

OldFactory::create();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static method is being called on a class that is marked as `@deprecated`. Deprecated classes are scheduled for removal or replacement. Calling static methods on them ties the code to an API that will eventually be removed.

## How to fix it

Use the recommended replacement class instead:

```diff-php
-OldFactory::create();
+NewFactory::create();
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	OldFactory::create();
 }
```
