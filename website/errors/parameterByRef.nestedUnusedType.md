---
title: "parameterByRef.nestedUnusedType"
shortDescription: "Declared output type of a by-reference parameter is wider than necessary in a nested part."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @param array<int|string> &$items
	 */
	public function process(array &$items): void
	{
		$items = [1, 2, 3];
	}
}
```

## Why is it reported?

The declared type of a by-reference parameter is wider than necessary in a nested part. PHPStan analyzed all code paths and determined that a narrower type would be more precise, because some union members in the nested type are never actually assigned to the parameter.

In the example above, the parameter type declares `array<int|string>`, but the function only ever assigns `int` values. The `string` part of the nested union is unused and the type could be narrowed to `array<int>`.

## How to fix it

Narrow the parameter type to match what the function actually assigns:

```diff-php
 class Foo
 {
 	/**
-	 * @param array<int|string> &$items
+	 * @param array<int> &$items
 	 */
 	public function process(array &$items): void
 	{
 		$items = [1, 2, 3];
 	}
 }
```
