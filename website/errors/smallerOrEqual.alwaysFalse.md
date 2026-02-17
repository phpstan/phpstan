---
title: "smallerOrEqual.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
    if ($i > 5) {
        if ($i <= 2) {
            // never reached
        }
    }
}
```

## Why is it reported?

The `<=` (less than or equal) comparison always evaluates to `false` because the types of the compared values make it impossible for the condition to be true. In this example, inside the `if ($i > 5)` branch, the variable `$i` is known to be of type `int<6, max>`. Comparing `$i <= 2` is always `false` because a value that is at least 6 can never be less than or equal to 2.

This usually indicates a logic error or a redundant check that can never be reached.

## How to fix it

Fix the comparison to use the correct value:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
     if ($i > 5) {
-        if ($i <= 2) {
+        if ($i <= 10) {
             // ...
         }
     }
 }
```

Or remove the unreachable condition entirely:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
     if ($i > 5) {
-        if ($i <= 2) {
-            // never reached
-        }
+        // execute code directly
     }
 }
```
