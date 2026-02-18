---
title: "staticProperty.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public static string $version = '1.0';
}

class MyClass
{
	use OldHelper;
}

echo OldHelper::$version;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A static property is accessed on a trait that is marked as `@deprecated`. Deprecated traits are scheduled for removal or replacement. Accessing static properties on them ties the code to an API that will eventually be removed.

## How to fix it

Use the recommended replacement trait or class instead:

```diff-php
-echo OldHelper::$version;
+echo NewHelper::$version;
```

If the calling code is itself deprecated, the error will not be reported. Mark the function or class as deprecated if it is part of a deprecation migration:

```diff-php
+/** @deprecated */
 function doFoo(): void
 {
 	echo OldHelper::$version;
 }
```
