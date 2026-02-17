---
title: "cast.useless"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value): void
{
    $result = (int) $value;
}
```

## Why is it reported?

The value being cast is already of the target type, making the cast redundant. In the example above, `$value` is already an `int`, so casting it to `int` with `(int)` has no effect.

This rule is part of [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) and helps identify unnecessary type casts that add clutter to the code without changing behaviour.

## How to fix it

Remove the unnecessary cast:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $value): void
 {
-    $result = (int) $value;
+    $result = $value;
 }
```
