---
title: "doWhile.condNotBoolean"
ignorable: true
---

This error is reported by `phpstan/phpstan-strict-rules`.

## Code example

```php
<?php declare(strict_types = 1);

$count = 5;

do {
    $count--;
} while ($count);
```

## Why is it reported?

The `phpstan-strict-rules` package enforces that conditions in `do-while` loops must be strictly boolean. Using a non-boolean value such as an integer or string relies on PHP's implicit truthiness rules, which can lead to subtle bugs.

## How to fix it

Use an explicit boolean comparison in the condition:

```diff-php
 <?php declare(strict_types = 1);

 $count = 5;

 do {
     $count--;
-} while ($count);
+} while ($count > 0);
```
