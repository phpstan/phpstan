---
title: "staticMethod.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public static function compute(): int
	{
		return 42;
	}
}

class MyClass
{
	use OldHelper;
}

function doFoo(): void
{
	OldHelper::compute();
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static method is being called on a trait that is marked as `@deprecated`. Deprecated traits are scheduled for removal or replacement. Calling static methods on them ties the code to an API that will eventually be removed.

## How to fix it

Use the recommended replacement trait or class instead:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	OldHelper::compute();
+	NewHelper::compute();
 }
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
 <?php declare(strict_types = 1);

+/** @deprecated */
 function doFoo(): void
 {
 	OldHelper::compute();
 }
```
