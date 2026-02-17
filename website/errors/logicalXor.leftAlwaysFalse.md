---
title: "logicalXor.leftAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $b): void
{
	if (0 xor $b) {
		// ...
	}
}
```

## Why is it reported?

The left side of the `xor` expression always evaluates to `false`. This means the result of the `xor` is equivalent to just the right side's boolean value, making the `xor` operator redundant.

The logical `xor` operator returns `true` when exactly one of its operands is `true`. If the left side is always `false`, the result is always equal to the right side.

## How to fix it

Remove the `xor` and use the right operand directly:

```diff-php
-if (0 xor $b) {
+if ($b) {
 	// ...
 }
```

If the constant value is unintentional, fix the left operand so it can actually be `true` or `false`:

```diff-php
-if (0 xor $b) {
+if ($a xor $b) {
 	// ...
 }
```
