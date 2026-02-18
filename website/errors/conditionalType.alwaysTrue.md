---
title: "conditionalType.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return ($i is int ? non-empty-array : array)
 */
function fill(int $i): array
{
	return [];
}
```

## Why is it reported?

The condition in a conditional return type PHPDoc tag is always true. In the example above, `$i` is declared as `int` in the native type, so the condition `$i is int` is always satisfied. This makes the conditional return type pointless because only the "true" branch will ever apply, and the condition adds unnecessary complexity without providing any type narrowing benefit.

## How to fix it

Remove the conditional return type and use the "true" branch directly:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @return ($i is int ? non-empty-array : array)
+ * @return non-empty-array
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
  * @return ($i is int ? non-empty-array : array)
  */
-function fill(int $i): array
+function fill(int|string $i): array
 {
 	return [];
 }
```
