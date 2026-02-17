---
title: "logicalOr.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $a, bool $b): void
{
	$result = $a or $b; // ERROR: Unused result of "or" operator.
}
```

## Why is it reported?

The `or` keyword has lower precedence than the `=` assignment operator. This means the expression `$result = $a or $b` is parsed as `($result = $a) or $b`, not as `$result = ($a or $b)`. The assignment of `$a` to `$result` happens first, and then the `or $b` part is evaluated but its result is discarded.

This is almost always a mistake where the developer intended to assign the result of the logical OR to the variable.

## How to fix it

Use `||` instead of `or` to get the expected precedence:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$result = $a or $b;
+	$result = $a || $b;
 }
```

Or add explicit parentheses to clarify the intended grouping:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$result = $a or $b;
+	$result = ($a or $b);
 }
```
