---
title: "postDec.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$arr = [];
$arr--;
```

## Why is it reported?

The post-decrement operator (`$x--`) cannot be used on certain types. For example, arrays, objects (other than `SimpleXMLElement`), and resources do not support decrement. On PHP 8.3+, decrementing non-numeric strings, `null`, and `bool` is also deprecated.

## How to fix it

Ensure the variable is of a type that supports the decrement operator:

```diff-php
-$arr = [];
-$arr--;
+$count = 10;
+$count--;
```

For non-numeric strings on PHP 8.3+, use `str_decrement()` instead:

```diff-php
-$str = 'b';
-$str--;
+$str = str_decrement('b');
```
