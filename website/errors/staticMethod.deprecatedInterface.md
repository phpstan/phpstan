---
title: "staticMethod.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewFactory instead */
interface OldFactory
{
	public static function create(): object;
}

class MyFactory implements OldFactory
{
	public static function create(): object
	{
		return new \stdClass();
	}
}

function doFoo(): void
{
	OldFactory::create();
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static method is being called on an interface that is marked as `@deprecated`. Deprecated interfaces are scheduled for removal or replacement. Calling static methods on them ties the code to an API that will eventually be removed.

## How to fix it

Use the recommended replacement interface or class instead:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	OldFactory::create();
+	NewFactory::create();
 }
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
 <?php declare(strict_types = 1);

+/** @deprecated */
 function doFoo(): void
 {
 	OldFactory::create();
 }
```
