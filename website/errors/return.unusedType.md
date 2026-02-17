---
title: "return.unusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): int|string
{
	if ($i > 0) {
		return $i;
	}

	return $i * -1;
}
```

## Why is it reported?

The declared return type contains a union type member that is never actually returned by the function or method. In the example above, the function declares `int|string` as its return type, but every code path returns an `int` value. The `string` part of the union is unused and makes the type declaration wider than necessary.

A too-wide return type reduces the type information available to callers, potentially forcing them to add unnecessary type checks.

## How to fix it

Narrow the return type by removing the type that is never returned:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): int|string
+function doFoo(int $i): int
 {
 	if ($i > 0) {
 		return $i;
 	}

 	return $i * -1;
 }
```

If the broader return type is intentional because the function may return the other type in the future or in subclass overrides, consider keeping the type and reviewing whether the implementation is complete.
