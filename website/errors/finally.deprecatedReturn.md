---
title: "finally.deprecatedReturn"
shortDescription: "Returning from a finally block is deprecated in PHP 8.6."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
	try {
		return computeValue();
	} finally {
		return 1;
	}
}
```

## Why is it reported?

A `return` statement inside a `finally` block is deprecated in PHP 8.6. A `return` in `finally` silently overrides the value returned from the `try` or `catch` block, and also discards any exception thrown from them. The exception is never seen by the caller, which makes bugs hard to find.

The error is reported for every `return` statement found directly in the `finally` block, including ones nested in `if`, loops, or a `try`/`catch` inside it. Returns inside closures, arrow functions, functions, and classes declared in the `finally` block are not reported.

The error is reported when the analysed PHP version is 8.6 or newer. See [`phpVersion`](/config-reference#phpversion) to configure which PHP versions are analysed.

## How to fix it

Move the `return` out of the `finally` block, and use `finally` only for cleanup:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): int
 {
 	try {
-		return computeValue();
+		$result = computeValue();
+	} catch (\Throwable $e) {
+		$result = 1;
 	} finally {
-		return 1;
+		cleanUp();
 	}
+
+	return $result;
 }
```

If exceptions from the `try` block should reach the caller, drop the `catch` block and keep only the cleanup in `finally`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): int
 {
 	try {
-		return computeValue();
+		$result = computeValue();
 	} finally {
-		return 1;
+		cleanUp();
 	}
+
+	return $result;
 }
```
