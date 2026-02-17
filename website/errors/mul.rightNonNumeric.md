---
title: "mul.rightNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var string $value */
$result = 5 * $value;
```

## Why is it reported?

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The right operand of the multiplication operator (`*`) is not a numeric type. PHP will attempt to cast non-numeric values to numbers, which can lead to unexpected results or warnings. Strict rules require that only numeric types are used in arithmetic operations.

## How to fix it

Ensure the operand is a numeric type:

```diff-php
 <?php declare(strict_types = 1);

-/** @var string $value */
-$result = 5 * $value;
+/** @var int $value */
+$result = 5 * $value;
```

Or explicitly cast the value:

```diff-php
 <?php declare(strict_types = 1);

 /** @var string $value */
-$result = 5 * $value;
+$result = 5 * (int) $value;
```
