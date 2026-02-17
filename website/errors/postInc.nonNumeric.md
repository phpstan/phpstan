---
title: "postInc.nonNumeric"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = false;
$value++; // ERROR: Only numeric types are allowed in post-increment, false given.
```

## Why is it reported?

The post-increment operator (`$variable++`) is intended for numeric types. Using it on non-numeric values like `false`, `null`, objects, or union types containing non-numeric members leads to unexpected behavior. In PHP, incrementing `null` produces `1`, incrementing `false` produces `1` (in PHP 8.3+, this is deprecated), and incrementing objects or other non-numeric types is either undefined or produces warnings.

This rule is provided by the [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) package.

## How to fix it

Use the post-increment operator only on numeric types (`int`, `float`, or numeric strings):

```diff-php
 <?php declare(strict_types = 1);

-$value = false;
+$value = 0;
 $value++;
```

If the variable might be non-numeric, initialize it properly before incrementing:

```diff-php
 <?php declare(strict_types = 1);

-$value = null;
-$value++;
+$value = 0;
+$value++;
```
