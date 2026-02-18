---
title: "plus.rightNonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$result = 1 + new stdClass();
```

## Why is it reported?

The `+` operator in PHP performs arithmetic addition on numeric types or array union on arrays. Using a non-numeric, non-array type on the right side of `+` is a type error that leads to unexpected behaviour or a runtime fatal error.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

## How to fix it

Ensure the right operand is a numeric type:

```diff-php
-$result = 1 + $input;
+$result = 1 + (int) $input;
```

Or use the correct operation for the intended types:

```diff-php
-$result = $string + $otherString;
+$result = $number + $otherNumber;
```
