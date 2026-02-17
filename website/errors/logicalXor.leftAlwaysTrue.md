---
title: "logicalXor.leftAlwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $b): void
{
	if (1 xor $b) {
		// ...
	}
}
```

## Why is it reported?

The left side of the `xor` expression always evaluates to `true`. This means the result of the `xor` is equivalent to the negation of the right side, making the `xor` operator misleading.

The logical `xor` operator returns `true` when exactly one of its operands is `true`. If the left side is always `true`, the result is always the opposite of the right side.

## How to fix it

Replace the `xor` with a negation of the right operand:

```diff-php
-if (1 xor $b) {
+if (!$b) {
 	// ...
 }
```

If the constant value is unintentional, fix the left operand so it can actually be `true` or `false`:

```diff-php
-if (1 xor $b) {
+if ($a xor $b) {
 	// ...
 }
```
