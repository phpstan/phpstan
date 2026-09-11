---
title: "catch.unusedVariableFlow"
shortDescription: "The exception variable of a catch block is read, but only to compute values that are themselves never used."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	try {
		mightThrow();
	} catch (\Exception $e) {
		while (rand(0, 1)) {
			$e = rand(0, 1) ? $e : null;
		}
	}
}

function mightThrow(): void
{
}
```

## Why is it reported?

The `catch` block binds the caught exception to `$e`, and that value *is* read — but only by `$e = rand(0, 1) ? $e : null`, whose result no code ever observes. The exception feeds a closed computation that produces nothing.

This is different from [`catch.unusedVariable`](/error-identifiers/catch.unusedVariable), where the exception variable is never read at all. Here the value flows through further computation, but that computation is itself dead, so binding the exception has no effect. This usually points to a logic error where the exception was meant to be used.

This is only reported on PHP 8.0 and later, where non-capturing catches are available.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use the exception in the `catch` block if it was intended to be read:

```diff-php
 	try {
 		mightThrow();
 	} catch (\Exception $e) {
-		while (rand(0, 1)) {
-			$e = rand(0, 1) ? $e : null;
-		}
+		echo $e->getMessage();
 	}
```

Or use a non-capturing `catch` (PHP 8.0+) when the exception is not needed:

```diff-php
 	try {
 		mightThrow();
-	} catch (\Exception $e) {
-		while (rand(0, 1)) {
-			$e = rand(0, 1) ? $e : null;
-		}
+	} catch (\Exception) {
+		// handle failure
 	}
```
