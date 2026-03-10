---
title: "parameter.deprecatedTrait"
shortDescription: "Parameter type references a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

class Foo
{
	/** @param OldHelper $x */
	public function doFoo($x): void
	{
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A function or method parameter has a PHPDoc type that references a trait marked as `@deprecated`. Deprecated traits are planned for removal in a future version, and parameter types should not rely on them.

## How to fix it

Replace the deprecated trait with a non-deprecated type in the PHPDoc tag:

```diff-php
 class Foo
 {
-	/** @param OldHelper $x */
+	/** @param NewHelper $x */
 	public function doFoo($x): void
 	{
 	}
 }
```
