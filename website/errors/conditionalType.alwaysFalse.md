---
title: "conditionalType.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return ($i is string ? non-empty-array : array)
 */
function fill(int $i): array
{
	return [];
}
```

## Why is it reported?

The condition in a conditional return type PHPDoc tag can never be true. In the example above, `$i` is declared as `int` in the native type, so the condition `$i is string` is always false -- an `int` value will never be a `string`. This makes the conditional return type pointless because only the "false" branch will ever apply, and it usually indicates an error in the PHPDoc annotation.

## How to fix it

Fix the condition to test a type that is actually possible for the parameter:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @return ($i is string ? non-empty-array : array)
+ * @return ($i is positive-int ? non-empty-array : array)
  */
 function fill(int $i): array
 {
 	return [];
 }
```

Or widen the parameter type so the condition becomes meaningful:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @return ($i is string ? non-empty-array : array)
  */
-function fill(int $i): array
+function fill(int|string $i): array
 {
 	return [];
 }
```
