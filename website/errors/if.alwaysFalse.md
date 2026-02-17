---
title: "if.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(int $value): void
{
    if ($value > 10) {
        return;
    }

    if ($value > 10) {
        echo 'large';
    }
}
```

## Why is it reported?

The `if` condition is always false based on the types and values PHPStan has inferred at that point in the code. The body of the `if` statement will never execute, making it dead code. This usually points to a logic error or redundant check.

## How to fix it

Review the surrounding logic and either remove the dead branch or fix the condition:

```diff-php
 <?php declare(strict_types = 1);

 function process(int $value): void
 {
     if ($value > 10) {
         return;
     }

-    if ($value > 10) {
-        echo 'large';
-    }
+    echo 'not large: ' . $value;
 }
```
