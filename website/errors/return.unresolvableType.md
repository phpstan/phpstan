---
title: "return.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    /**
     * @return T
     */
    public function doFoo(): mixed
    {
        return null;
    }
}
```

## Why is it reported?

The PHPDoc `@return` tag contains a type that PHPStan cannot resolve. This typically happens when a template type (generic type parameter) like `T` is referenced but never declared with a `@template` tag, or when a type alias or class name cannot be found.

An unresolvable type in the `@return` tag means PHPStan cannot verify the correctness of the return type, which defeats the purpose of the type annotation.

## How to fix it

Declare the template type with a `@template` tag if you are writing a generic method:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
+     * @template T
+     * @param class-string<T> $class
      * @return T
      */
-    public function doFoo(): mixed
+    public function doFoo(string $class): mixed
     {
-        return null;
+        return new $class();
     }
 }
```

Or replace the unresolvable type with a valid type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     /**
-     * @return T
+     * @return mixed
      */
     public function doFoo(): mixed
     {
         return null;
     }
 }
```
