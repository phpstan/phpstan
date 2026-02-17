---
title: "booleanNot.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
    $zero = 0;
    if (!$zero) {
        // always entered
    }
}
```

## Why is it reported?

The negated boolean expression (`!$zero`) always evaluates to `true` because the operand is always falsy. In this example, the variable `$zero` is always `0`, which is falsy in PHP, so `!$zero` is always `true`. This means the condition will always be entered, which usually indicates a logic error or a redundant check.

## How to fix it

Remove the redundant condition if it is always evaluating to the same value:

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
    $zero = 0;
    // Execute the code unconditionally instead of wrapping in if (!$zero)
}
```

Or fix the logic to check the correct variable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-    $zero = 0;
-    if (!$zero) {
+    if (!$i) {
         // now depends on the actual input
     }
 }
```
