---
title: "method.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public function help(): void
	{
	}
}

class Foo
{
	use OldHelper;
}

function doFoo(Foo $foo): void
{
	$foo->help(); // ERROR: Call to method help() of deprecated trait OldHelper.
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The method being called is declared in a trait that has been marked as deprecated with the `@deprecated` PHPDoc tag. Even though the method itself may not be deprecated, calling it through a class that uses a deprecated trait signals usage of deprecated functionality. The deprecation notice typically suggests a replacement to use instead.

## How to fix it

Replace the usage of the deprecated trait with the suggested replacement:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	use OldHelper;
+	use NewHelper;
 }
```

Or call a method from a non-deprecated source if one is available.
