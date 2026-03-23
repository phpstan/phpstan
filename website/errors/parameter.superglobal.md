---
title: "parameter.superglobal"
shortDescription: "Using a superglobal variable name as a function or method parameter."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo($_GET): void
{
}
```

## Why is it reported?

PHP superglobal variables (`$_GET`, `$_POST`, `$_SERVER`, `$_FILES`, `$_COOKIE`, `$_SESSION`, `$_REQUEST`, `$_ENV`, `$GLOBALS`) have special meaning in the language. Using one of these names as a function or method parameter shadows the superglobal, making the global value inaccessible inside the function and creating confusing code. PHP itself may also produce unexpected behaviour when a superglobal name is reused as a parameter.

## How to fix it

Rename the parameter to something descriptive that does not collide with a superglobal:

```diff-php
-function doFoo($_GET): void
+function doFoo(array $queryParams): void
 {
 }
```
