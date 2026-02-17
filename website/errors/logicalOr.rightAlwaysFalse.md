---
title: "logicalOr.rightAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function check(int $value): bool
{
	return $value > 0 or $value > 10;
}
```

## Why is it reported?

The right side of the `or` operator always evaluates to `false`. This typically happens because the left side already covers all the cases where the right side could be `true`, or because the type of the expression on the right side is narrowed to a point where it can never be `true`. The condition is redundant and likely indicates a logic error.

## How to fix it

Review the logic and either remove the redundant right side or correct the condition so that it can actually evaluate to `true`.

```diff-php
 <?php declare(strict_types = 1);

 function check(int $value): bool
 {
-	return $value > 0 or $value > 10;
+	return $value > 0 or $value < -10;
 }
```
