---
title: "postDec.nonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @var stdClass $object */
$object--;
```

## Why is it reported?

The post-decrement operator (`$x--`) is intended for numeric types. Using it on a non-numeric value such as an object, array, or non-numeric string produces unexpected results or a runtime error.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

## How to fix it

Ensure the operand is a numeric type:

```diff-php
-$value--;
+$numericValue--;
```

If the value might be non-numeric, convert it first:

```diff-php
-$value--;
+$value = ((int) $value) - 1;
```
