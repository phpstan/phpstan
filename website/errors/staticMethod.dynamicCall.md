---
title: "staticMethod.dynamicCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public static function bar(): void
    {
    }
}

function doFoo(Foo $foo): void
{
    $foo->bar();
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-strict-rules`.

A static method is being called dynamically using `$instance->method()` syntax instead of the proper `ClassName::method()` static call syntax. While PHP allows this, it is misleading because it suggests the method operates on the instance when it actually does not. Static methods do not have access to `$this` and calling them dynamically obscures the code's intent.

## How to fix it

Call the method using the static call syntax:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(Foo $foo): void
 {
-    $foo->bar();
+    Foo::bar();
 }
```
