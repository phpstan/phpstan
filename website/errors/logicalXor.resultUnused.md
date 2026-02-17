---
title: "logicalXor.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $a, bool $b): void
{
	$r = $a xor $b;
}
```

## Why is it reported?

The result of the `xor` operator is not being used. The `xor` operator in PHP has a very low precedence -- lower than the assignment operator `=`. This means the expression `$r = $a xor $b` is actually parsed as `($r = $a) xor $b`, which assigns `$a` to `$r` and then discards the result of the `xor` operation.

This is almost certainly not the intended behaviour. The same precedence issue applies to the `and` and `or` operators.

## How to fix it

Use parentheses to disambiguate the logic:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$r = $a xor $b;
+	$r = ($a xor $b);
 }
```

Or use the `^` operator instead, which has higher precedence and works as expected with booleans:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $a, bool $b): void
 {
-	$r = $a xor $b;
+	$r = $a ^ $b;
 }
```
