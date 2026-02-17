---
title: "staticClassAccess.privateConstant"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    private const FOO = 1;

    public function doFoo(): int
    {
        return static::FOO;
    }
}
```

## Why is it reported?

Accessing a private constant through `static::` is unsafe in a non-final class. The `static` keyword uses late static binding, which resolves to the actual class at runtime. If a child class extends `Foo`, `static::FOO` in the child's context would try to access `Foo::FOO`, but private constants are not visible to child classes. This leads to a fatal error at runtime.

## How to fix it

Use `self::` instead of `static::` to access private constants, since they cannot be overridden in child classes:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     private const FOO = 1;

     public function doFoo(): int
     {
-        return static::FOO;
+        return self::FOO;
     }
 }
```

Alternatively, make the class final if it is not intended to be extended:

```diff-php
 <?php declare(strict_types = 1);

-class Foo
+final class Foo
 {
     private const FOO = 1;

     public function doFoo(): int
     {
         return static::FOO;
     }
 }
```
