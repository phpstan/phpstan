---
title: "impure.global"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function getDbConnection(): object
{
	global $db; // ERROR: Impure global variable in pure function getDbConnection().

	return $db;
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Using the `global` keyword inside a pure function is an impure operation because it accesses mutable global state. The value of a global variable can change between calls, which means the function's return value may differ even when called with the same arguments. This violates the contract of a pure function.

## How to fix it

Pass the dependency as a parameter instead of accessing it via `global`:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function getDbConnection(): object
+function getDbConnection(object $db): object
 {
-	global $db;
-
 	return $db;
 }
```

Or remove the `@phpstan-pure` annotation if the function genuinely needs to access global state:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function getDbConnection(): object
 {
 	global $db;

 	return $db;
 }
```
