---
title: "cast.deprecated"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = (integer) '42';
$flag = (boolean) 1;
$rate = (double) '3.14';
```

## Why is it reported?

PHP 8.5 deprecates non-standard cast syntax variants. The casts `(integer)`, `(boolean)`, `(double)`, and `(binary)` are aliases for `(int)`, `(bool)`, `(float)`, and `(string)` respectively. While they have worked historically, they are now deprecated and will be removed in a future PHP version.

## How to fix it

Replace the deprecated cast syntax with the standard form:

```diff-php
 <?php declare(strict_types = 1);

-$value = (integer) '42';
-$flag = (boolean) 1;
-$rate = (double) '3.14';
+$value = (int) '42';
+$flag = (bool) 1;
+$rate = (float) '3.14';
```
