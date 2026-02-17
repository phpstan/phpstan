---
title: "print.nonString"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$values = [1, 2, 3];
print $values;
```

## Why is it reported?

The argument passed to `print` cannot be converted to a string. In the example above, `$values` is an `array`, which does not have a `__toString()` method and cannot be implicitly converted to a string.

## How to fix it

Convert the value to a string before printing:

```diff-php
 <?php declare(strict_types = 1);

 $values = [1, 2, 3];
-print $values;
+print implode(', ', $values);
```
