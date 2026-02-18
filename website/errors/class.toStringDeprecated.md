---
title: "class.toStringDeprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @deprecated
	 */
	public function __toString(): string
	{
		return 'foo';
	}
}

function doFoo(Foo $foo): string
{
	return (string) $foo;
}
```

## Why is it reported?

The `__toString()` method on the class is marked as `@deprecated`. Casting an instance of this class to a string (via `(string)`, string interpolation, `echo`, or any implicit string conversion) invokes the deprecated method. This rule is part of the `phpstan-deprecation-rules` package and warns about usages of deprecated string conversions so they can be replaced before the method is removed.

## How to fix it

Use an explicit method to get the string representation instead of relying on the deprecated `__toString()`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(Foo $foo): string
 {
-	return (string) $foo;
+	return $foo->getName();
 }
```
