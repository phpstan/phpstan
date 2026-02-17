---
title: "ternary.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
    $zero = 0;
    $result = $zero ? 'yes' : 'no';
}
```

## Why is it reported?

The condition of the ternary operator always evaluates to `false`, which means the "true" branch can never be reached. This typically indicates a logic error, dead code, or a condition that does not express the intended check.

In the example above, `$zero` is assigned `0`, which is always falsy in PHP, so the ternary will always evaluate to `'no'`.

## How to fix it

Fix the condition so it can actually evaluate to both `true` and `false`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(int $value): void
 {
-    $zero = 0;
-    $result = $zero ? 'yes' : 'no';
+    $result = $value > 0 ? 'yes' : 'no';
 }
```

Or if the condition is intentionally always false, simplify the code by using the false-branch value directly:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    $zero = 0;
-    $result = $zero ? 'yes' : 'no';
+    $result = 'no';
 }
```
