---
title: "equal.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
    if (0 == '0') { // error: Loose comparison using == between 0 and '0' will always evaluate to true.
        // ...
    }
}
```

## Why is it reported?

A loose comparison using `==` is always `true` based on the types of the compared values. This means the condition is redundant -- it will never evaluate to `false`. This usually indicates a logic error, a misunderstanding of PHP's type juggling rules, or code that has become dead after refactoring.

PHP's loose comparison (`==`) performs type coercion before comparing, which can lead to surprising results. For example, `0 == '0'` is always `true` because PHP converts the string to an integer for comparison.

## How to fix it

If the comparison is intentional, use strict comparison (`===`) which does not perform type coercion:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    if (0 == '0') {
+    if (0 === 0) {
         // ...
     }
 }
```

If the condition is always true, the conditional branch is unnecessary and can be simplified:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    if (0 == '0') {
-        // ...
-    }
+    // ...
 }
```
