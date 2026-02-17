---
title: "greaterOrEqual.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function check(int $value): void
{
    if ($value >= 0) {
        return;
    }

    if ($value >= 0) {
        echo 'non-negative';
    }
}
```

## Why is it reported?

The `>=` comparison is always false based on the types of the compared values. PHPStan determined from the control flow that the condition can never be true. This indicates dead code or a logic error.

## How to fix it

Review the logic and remove the always-false condition, or fix the comparison:

```diff-php
 <?php declare(strict_types = 1);

 function check(int $value): void
 {
     if ($value >= 0) {
         return;
     }

-    if ($value >= 0) {
-        echo 'non-negative';
-    }
+    echo 'negative: ' . $value;
 }
```
