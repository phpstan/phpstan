---
title: "catch.unusedVariable"
shortDescription: "The exception variable of a catch block is never read."
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
		echo 'Something went wrong';
	}
}

function mightThrow(): void
{
}
```

## Why is it reported?

The `catch` block captures the caught exception into `$e`, but never reads it. Since PHP 8.0 the exception variable can be omitted entirely with a non-capturing `catch`, so binding a variable that nobody uses is unnecessary.

This is only reported on PHP 8.0 and later, where non-capturing catches are available.

This rule is part of PHPStan's dead code analysis. It is reported at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Use a non-capturing `catch` (PHP 8.0+) when the exception is not needed:

```diff-php
 	try {
 		mightThrow();
-	} catch (\Exception $e) {
+	} catch (\Exception) {
 		echo 'Something went wrong';
 	}
```

Or use the exception in the `catch` block if it was intended to be read:

```diff-php
 	try {
 		mightThrow();
 	} catch (\Exception $e) {
-		echo 'Something went wrong';
+		echo $e->getMessage();
 	}
```
