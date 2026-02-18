---
title: "parameterByRef.nestedUnusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param array<mixed> $a
 * @param-out array<array{int, bool}> $a
 */
function doFoo(array &$a): void
{
	$a = [
		[1, false],
		[2, false],
	];
}
```

## Why is it reported?

The declared `@param-out` type for a by-reference parameter is wider than necessary. The nested type contains a union member or component that is never actually assigned to the parameter. PHPStan analyzed all code paths and determined that a narrower type would be more precise.

In the example above, if the `bool` in `array{int, bool}` is always `false`, the type could be narrowed to `array{int, false}`.

## How to fix it

Narrow the `@param-out` type to match what the function actually assigns:

```diff-php
 /**
  * @param array<mixed> $a
- * @param-out array<array{int, bool}> $a
+ * @param-out array<array{int, false}> $a
  */
 function doFoo(array &$a): void
 {
 	$a = [
 		[1, false],
 		[2, false],
 	];
 }
```
