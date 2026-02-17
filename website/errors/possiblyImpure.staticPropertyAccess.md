---
title: "possiblyImpure.staticPropertyAccess"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Config
{
	public static bool $debug = false;
}

/**
 * @phpstan-pure
 */
function getLabel(string $name): string
{
	if (Config::$debug) { // ERROR: Possibly impure static property access in pure function getLabel().
		return '[DEBUG] ' . $name;
	}

	return $name;
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Accessing a static property inside a pure function is considered impure because static properties represent shared mutable state. Their values can change between function calls, which means the function's return value may differ even when called with the same arguments.

When PHPStan cannot determine with certainty whether the static property access always occurs (for example, because it is inside a conditional branch), it reports a "possibly impure" error rather than a definite impure one.

## How to fix it

Pass the value as a parameter instead of reading a static property:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function getLabel(string $name): string
+function getLabel(string $name, bool $debug): string
 {
-	if (Config::$debug) {
+	if ($debug) {
 		return '[DEBUG] ' . $name;
 	}

 	return $name;
 }
```

Or remove the `@phpstan-pure` annotation if the function genuinely needs to access static properties:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function getLabel(string $name): string
 {
 	if (Config::$debug) {
 		return '[DEBUG] ' . $name;
 	}

 	return $name;
 }
```
