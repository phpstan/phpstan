---
title: "staticMethod.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
	case Active = 'active';

	public static function getDefault(): self
	{
		return self::Active;
	}
}

OldStatus::getDefault();
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static method is being called on an enum that is marked as `@deprecated`. Deprecated enums are scheduled for removal or replacement. Calling static methods on them ties the code to an API that will eventually be removed.

This error can also be reported when calling a deprecated static method on an enum, regardless of whether the enum itself is deprecated.

## How to fix it

Use the recommended replacement enum:

```diff-php
-OldStatus::getDefault();
+NewStatus::getDefault();
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	OldStatus::getDefault();
 }
```
