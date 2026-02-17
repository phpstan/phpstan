---
title: "staticProperty.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int|string $value): void
{
    echo $value::$foo;
}
```

## Why is it reported?

The code attempts to access a static property on a value that is not an object or a class string. Static properties can only be accessed on class types. Accessing a static property on a non-object type such as `int`, `float`, `bool`, or a non-class `string` results in a fatal error at runtime.

## How to fix it

Ensure the variable holds an object or class-string type before accessing static properties:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int|string $value): void
+function doFoo(Foo $value): void
 {
     echo $value::$foo;
 }
```
