---
title: "catch.alreadyCaught"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	try {
		doSomething();
	} catch (\Throwable $e) {

	} catch (\TypeError $e) {

	}
}
```

## Why is it reported?

PHP evaluates `catch` blocks in order from top to bottom and enters the first one whose type matches the thrown exception. In the example above, `\Throwable` is a supertype of `\TypeError`, so the first `catch` block will always match any `TypeError` before the second block is reached. The second `catch` block is dead code and will never execute.

## How to fix it

Reorder the catch blocks so that more specific exception types come first:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
 	try {
 		doSomething();
-	} catch (\Throwable $e) {
-
 	} catch (\TypeError $e) {
+		// Handle TypeError specifically
+	} catch (\Throwable $e) {
+		// Handle everything else
 	}
 }
```

Or remove the dead catch block if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
 	try {
 		doSomething();
 	} catch (\Throwable $e) {

-	} catch (\TypeError $e) {
-
 	}
 }
```
