---
title: "logicalXor.rightAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(object $a): void
{
	if ($a xor true) {
		// ...
	}
}
```

## Why is it reported?

The right side of the `xor` operator is always true. Since one operand is always true, the `xor` expression effectively becomes the negation of the other operand (`!$a` in this case). This is misleading and likely indicates a logic error.

The `xor` operator returns true when exactly one of its operands is true. If one operand is always true, the result is simply the inverse of the other operand.

## How to fix it

If negation is intended, use the `!` operator for clarity:

```diff-php
-	if ($a xor true) {
+	if (!$a) {
 		// ...
 	}
```

If the condition should actually test two dynamic values, fix the right operand:

```diff-php
-	if ($a xor true) {
+	if ($a xor $b) {
 		// ...
 	}
```
