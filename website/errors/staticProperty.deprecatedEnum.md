---
title: "staticProperty.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
	case Active = 'active';

	public static string $label = 'Status';
}

echo OldStatus::$label;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static property is accessed on an enum that is marked as `@deprecated`. Deprecated enums are scheduled for removal or replacement, and code should not rely on them.

## How to fix it

Use the recommended replacement enum or class:

```diff-php
-echo OldStatus::$label;
+echo NewStatus::$label;
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	echo OldStatus::$label;
 }
```
