---
title: "logicalOr.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$result = ($i >= 0) or ($i < 0); // ERROR: Result of or is always true.
}
```

## Why is it reported?

PHPStan determined that the result of the `or` expression is always `true`. At least one side of the operator is guaranteed to be truthy regardless of the input. In the example above, every integer is either greater than or equal to zero, or less than zero, so the entire expression is always `true`. This usually indicates a logic error, a redundant check, or dead code.

The `or` keyword is the low-precedence version of `||`. This identifier specifically covers the `or` keyword; for `||`, see [`booleanOr.alwaysTrue`](/errors/booleanOr.alwaysTrue).

## How to fix it

Fix the logic to produce a meaningful condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$result = ($i >= 0) or ($i < 0);
+	$result = ($i >= 0) or ($i === -1);
 }
```

Or simplify the expression if the result is intentionally always `true`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$result = ($i >= 0) or ($i < 0);
+	$result = true;
 }
```
