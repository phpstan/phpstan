---
title: "while.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	while (true) {

	}
}
```

## Why is it reported?

The condition of the `while` loop always evaluates to `true`, and the loop does not contain a `break` statement or other exit point. This creates an infinite loop that will never terminate, which is almost always a bug.

If the enclosing function has a `never` return type, this error is not reported because the infinite loop is intentional.

## How to fix it

Add an exit condition to the loop:

```diff-php
-function doFoo(): void
+function doFoo(bool $running): void
 {
-	while (true) {
-
+	while ($running) {
+		// do something
+		$running = false;
 	}
 }
```

Or add a `break` statement inside the loop body:

```diff-php
 function doFoo(): void
 {
 	while (true) {
-
+		// do something
+		if ($condition) {
+			break;
+		}
 	}
 }
```

If the infinite loop is intentional (e.g., for a long-running daemon), declare the function return type as `never`:

```diff-php
-function doFoo(): void
+function doFoo(): never
 {
 	while (true) {
-
+		// process requests
 	}
 }
```
