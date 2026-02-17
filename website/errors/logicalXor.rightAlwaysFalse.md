---
title: "logicalXor.rightAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function test(bool $a): bool
{
	return $a xor ($a && !$a);
}
```

## Why is it reported?

The right side of the `xor` operator always evaluates to `false`. This makes the `xor` expression equivalent to just the left side, because `$left xor false` always equals `$left`. The right operand is redundant and likely indicates a logic error.

## How to fix it

Review the logic and either remove the `xor` expression entirely if the right side is not needed, or correct the right-side condition so that it can actually evaluate to `true`.

```diff-php
 <?php declare(strict_types = 1);

 function test(bool $a): bool
 {
-	return $a xor ($a && !$a);
+	return $a xor someOtherCondition();
 }
```
