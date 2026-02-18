---
title: "booleanOr.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
    if (is_string($i) || is_array($i)) {
        // ...
    }
}
```

## Why is it reported?

The result of the `||` (boolean or) expression is always `false`. Both the left and right sides of the expression evaluate to `false` for every possible value, which means the condition can never be satisfied and the code inside the `if` block is dead code. This typically happens when the types involved make one or both sides of the expression impossible to be truthy.

## How to fix it

Fix the condition to match the intended logic:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int|string|array $i): void
 {
     if (is_string($i) || is_array($i)) {
         // ...
     }
 }
```

Or remove the always-false condition entirely:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-    if (is_string($i) || is_array($i)) {
-        // ...
-    }
 }
```
