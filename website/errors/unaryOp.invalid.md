---
title: "unaryOp.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(array $a): void
{
	-$a;
}
```

## Why is it reported?

A unary operator (`+`, `-`, or `~`) is applied to a value whose type does not support that operation. PHP's unary operators have specific type requirements:

- `+` (unary plus) and `-` (unary minus) require a numeric type (int, float, or a string that can be converted to a number).
- `~` (bitwise not) requires an int, float, or string.

Applying these operators to incompatible types like arrays, objects, or resources results in a runtime error.

## How to fix it

Ensure the operand has a type compatible with the unary operator.

```diff-php
-function doFoo(array $a): void
+function doFoo(int $a): void
 {
 	-$a;
 }
```

Or convert the value to an appropriate type before applying the operator:

```diff-php
 function doFoo(string $value): void
 {
-	-$value;
+	-(int) $value;
 }
```
