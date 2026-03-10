---
title: "method.deprecatedTrait"
shortDescription: "Called method belongs to a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void {}
}

class Foo
{
	/** @param OldHelper $x */
	public function doFoo($x): void
	{
		$x->help();
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A method is called on a value whose type is a deprecated trait. The trait has been marked with `@deprecated`, indicating it is scheduled for removal. Calling methods on deprecated trait types means the code depends on functionality that will eventually be removed.

## How to fix it

Replace the deprecated trait type with its recommended replacement:

```diff-php
 class Foo
 {
-	/** @param OldHelper $x */
+	/** @param NewHelper $x */
 	public function doFoo($x): void
 	{
 		$x->help();
 	}
 }
```
