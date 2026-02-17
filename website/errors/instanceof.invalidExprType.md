---
title: "instanceof.invalidExprType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(object $obj, int $type): void
{
	if ($obj instanceof $type) {
		// ...
	}
}
```

## Why is it reported?

The right-hand side of `instanceof` is an expression whose type is neither a string nor an object. PHP requires the right-hand side of `instanceof` to be either a class name (string) or an object instance. Using a value of type `int`, `array`, or other non-string/non-object types will result in a runtime error.

## How to fix it

Pass a class name string or an object:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(object $obj, int $type): void
+function doFoo(object $obj, string $type): void
 {
 	if ($obj instanceof $type) {
 		// ...
 	}
 }
```
