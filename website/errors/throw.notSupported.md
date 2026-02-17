---
title: "throw.notSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(bool $condition): string
{
    $value = $condition ? 'hello' : throw new \Exception('Not valid');
}
```

## Why is it reported?

Throw expressions (using `throw` as an expression rather than a statement) were introduced in PHP 8.0. In earlier versions of PHP, `throw` can only be used as a standalone statement. Using `throw` inside a ternary expression, null coalescing operator, or short closure is a syntax error on PHP versions before 8.0.

This error is reported when PHPStan is configured to analyse code for a PHP version that does not support throw expressions.

## How to fix it

If the project requires compatibility with PHP versions before 8.0, rewrite the code to use `throw` as a statement:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(bool $condition): string
 {
-    $value = $condition ? 'hello' : throw new \Exception('Not valid');
+    if (!$condition) {
+        throw new \Exception('Not valid');
+    }
+    $value = 'hello';
 }
```
