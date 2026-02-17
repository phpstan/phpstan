---
title: "identical.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i === 'hello') {
		// ...
	}
}
```

## Why is it reported?

A strict comparison using `===` between two values of incompatible types will always evaluate to `false`. The `===` operator checks both value and type equality, so comparing an `int` with a `string` can never be `true`.

In the example above, `$i` is an `int` and `'hello'` is a `string`. These types can never be identical, so the condition will never be satisfied and the code inside the `if` block is dead code.

## How to fix it

Fix the comparison to use the correct type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	if ($i === 'hello') {
+	if ($i === 42) {
 		// ...
 	}
 }
```

Or fix the parameter type if the comparison is correct:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(string $i): void
 {
 	if ($i === 'hello') {
 		// ...
 	}
 }
```
