---
title: "classConstant.nameType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public const BAR = 1;
}

function doFoo(int $name): void
{
    echo Foo::{$name};
}
```

## Why is it reported?

PHP 8.3 introduced dynamic class constant fetching using the syntax `ClassName::{$expr}`. The expression used as the constant name must evaluate to a `string`. Passing a non-string value such as an `int` is not valid because class constant names are always strings.

## How to fix it

Ensure the expression used for the dynamic constant name is a `string`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $name): void
+function doFoo(string $name): void
 {
     echo Foo::{$name};
 }
```

Or cast the value to a string:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $name): void
 {
-    echo Foo::{$name};
+    echo Foo::{(string) $name};
 }
```
