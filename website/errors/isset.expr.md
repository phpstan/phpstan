---
title: "isset.expr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value): void
{
	if (isset($value)) {
		// ...
	}
}
```

## Why is it reported?

The expression inside `isset()` is never `null` based on the types PHPStan has inferred, so the `isset()` check is unnecessary -- it will always evaluate to `true`. The variable `$value` is typed as `int`, which cannot be `null`, so `isset($value)` serves no purpose.

The same applies to `empty()` checks and `??` (null coalescing) expressions when the left side is never `null`.

## How to fix it

Remove the unnecessary `isset()` check if the value is always defined and non-null:

```diff-php
-	if (isset($value)) {
+	if (true) { // or simply remove the condition
 		// ...
 	}
```

If the value should be nullable, update the type declaration:

```diff-php
-function doFoo(int $value): void
+function doFoo(?int $value): void
 {
 	if (isset($value)) {
 		// ...
 	}
 }
```
