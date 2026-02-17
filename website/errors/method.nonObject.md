---
title: "method.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int|string $value): void
{
	$value->toString(); // ERROR: Cannot call method toString() on int|string.
}
```

## Why is it reported?

A method is being called on a value whose type does not support method calls. Methods can only be called on objects. In the example above, `$value` is `int|string`, neither of which is an object type, so calling a method on it is invalid.

## How to fix it

Narrow the type to an object before calling the method:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int|string $value): void
+function doFoo(int|string|Stringable $value): void
 {
-	$value->toString();
+	if ($value instanceof Stringable) {
+		$value->toString();
+	}
 }
```

Or change the parameter type to accept only objects that have the desired method:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int|string $value): void
+function doFoo(Stringable $value): void
 {
 	$value->toString();
 }
```
