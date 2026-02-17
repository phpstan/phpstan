---
title: "finally.exitPoint"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): string
{
	try {
		if (rand(0, 1)) {
			throw new \Exception();
		}

		return 'foo';
	} catch (\Exception $e) {
		return 'bar';
	} finally {
		return 'baz';
	}
}
```

## Why is it reported?

A `return`, `throw`, or other exit point inside a `try` or `catch` block is overwritten by an exit point in the `finally` block. When a `finally` block contains a `return` or `throw`, it always takes precedence over any `return` or `throw` from the `try` or `catch` blocks, effectively silencing exceptions and discarding return values.

In the example above, the `return 'foo'` in the `try` block and `return 'bar'` in the `catch` block are both overwritten by `return 'baz'` in the `finally` block. The function will always return `'baz'`, which is almost certainly not the intended behavior.

## How to fix it

Remove the exit point from the `finally` block, since `finally` is meant for cleanup logic, not for controlling the return flow:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): string
 {
 	try {
 		if (rand(0, 1)) {
 			throw new \Exception();
 		}

 		return 'foo';
 	} catch (\Exception $e) {
 		return 'bar';
 	} finally {
-		return 'baz';
+		// Perform cleanup here, but do not return
 	}
 }
```
