---
title: "impure.staticPropertyAccess"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Counter
{
	public static int $count = 0;
}

/**
 * @phpstan-pure
 */
function getCount(): int
{
	return Counter::$count; // ERROR: Impure static property access in pure function getCount().
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Accessing a static property inside a pure function is considered impure because static properties represent shared mutable state. Their values can change between function calls, which means the function's return value may differ even when called with the same arguments.

## How to fix it

Pass the value as a parameter instead of reading a static property:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function getCount(): int
+function getCount(int $count): int
 {
-	return Counter::$count;
+	return $count;
 }
```

Or remove the `@phpstan-pure` annotation if the function genuinely needs to access static properties:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function getCount(): int
 {
 	return Counter::$count;
 }
```
