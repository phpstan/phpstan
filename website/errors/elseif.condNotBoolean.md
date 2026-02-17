---
title: "elseif.condNotBoolean"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function classify(string $value): string
{
    if ($value === 'admin') {
        return 'Administrator';
    } elseif ($value) { // error: Only booleans are allowed in an elseif condition, string given.
        return 'User';
    }

    return 'Guest';
}
```

This rule is provided by the package [`phpstan/phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules).

## Why is it reported?

PHP performs implicit type coercion when evaluating conditions. Values like `0`, `''`, `'0'`, `[]`, and `null` are considered falsy. This implicit coercion can mask bugs -- for example, the string `'0'` is falsy, which may not be the intended behaviour.

Requiring explicit boolean expressions in `elseif` conditions makes the code's intent clearer and avoids subtle errors from truthy/falsy coercion.

## How to fix it

Use an explicit comparison that returns a boolean:

```diff-php
 <?php declare(strict_types = 1);

 function classify(string $value): string
 {
     if ($value === 'admin') {
         return 'Administrator';
-    } elseif ($value) {
+    } elseif ($value !== '') {
         return 'User';
     }

     return 'Guest';
 }
```
