---
title: "paramOut.nestedUnusedType"
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

The `@param-out` type declaration is wider than necessary in a nested part of the type. In the example above, the `@param-out` type declares `array<array{int, bool}>`, but the function only ever assigns `false` to the second element of the inner tuple, never `true`. The type could be narrowed to `array<array{int, false}>`.

This is similar to having a too-wide return type, but it applies to by-reference parameter output types and specifically to nested type components within the declared type.

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
