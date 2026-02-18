---
title: "preDec.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$obj = new stdClass();
--$obj;

$arr = [];
--$arr;
```

## Why is it reported?

The pre-decrement operator (`--`) can only be used on variables of type `int`, `float`, `string` (numeric strings only), or `null`. Using it on other types such as objects, arrays, booleans, or resources is not a valid operation and may lead to unexpected behaviour or deprecation notices in newer PHP versions.

## How to fix it

Ensure the variable is of a type that supports the decrement operator. If the value is supposed to be a number, assign or cast it to the correct type first:

```diff-php
 <?php declare(strict_types = 1);

-$value = new stdClass();
---$value;
+$value = 1;
+--$value;
```

If the variable is a string that should be decremented, use `str_decrement()` (PHP 8.3+) instead:

```diff-php
 <?php declare(strict_types = 1);

 $str = 'b';
---$str;
+$str = str_decrement($str);
```
