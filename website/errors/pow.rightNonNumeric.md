---
title: "pow.rightNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$base = 2;
$exponent = null;

$result = $base ** $exponent;
```

## Why is it reported?

This error is reported by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) extension.

The right operand of the `**` (exponentiation) operator is not a numeric type. PHP will attempt to coerce the value to a number, which can produce unexpected results or warnings. Strict type checking ensures that only `int` or `float` values are used in arithmetic operations.

## How to fix it

Ensure the right operand is a numeric type:

```diff-php
 $base = 2;
-$exponent = null;
+$exponent = 3;

 $result = $base ** $exponent;
```

If the value comes from an external source, validate or cast it before use:

```diff-php
-$result = $base ** $input;
+$result = $base ** (int) $input;
```
