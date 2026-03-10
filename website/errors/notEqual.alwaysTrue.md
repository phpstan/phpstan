---
title: "notEqual.alwaysTrue"
shortDescription: "Loose comparison using != always evaluates to true."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @param positive-int $i */
function doFoo(int $i): void
{
	if ($i != 0) {
		echo 'always nonzero';
	}
}
```

## Why is it reported?

The loose comparison using `!=` always evaluates to `true` based on the types PHPStan has inferred. This means the condition is always satisfied, making it redundant, and any `else` branch would be dead code.

The `!=` operator performs a loose (type-juggling) comparison. PHPStan reports this when it can determine from the types that the two values can never be loosely equal, regardless of the actual runtime values.

In the example above, `$i` is a `positive-int` (always `>= 1`), so `$i != 0` is always `true`.

## How to fix it

Remove the redundant condition:

```diff-php
 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i != 0) {
-		echo 'always nonzero';
-	}
+	echo 'always nonzero';
 }
```

Or fix the comparison value so the condition becomes meaningful:

```diff-php
 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i != 0) {
+	if ($i != 1) {
 		echo 'not one';
 	}
 }
```

Consider using strict comparison (`!==`) instead of loose comparison, which avoids type-juggling surprises:

```diff-php
 /** @param positive-int $i */
 function doFoo(int $i): void
 {
-	if ($i != 0) {
+	if ($i !== 1) {
 		// ...
 	}
 }
```
