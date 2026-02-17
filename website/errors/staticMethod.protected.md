---
title: "staticMethod.protected"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    protected static function baz(): void
    {
    }
}

class Unrelated
{
    public function doFoo(): void
    {
        Foo::baz();
    }
}
```

## Why is it reported?

The code calls a protected static method from a class that is not part of the declaring class hierarchy. Protected methods can only be called from within the declaring class or its descendants. Calling a protected method from an unrelated class results in a fatal error at runtime.

## How to fix it

If the method needs to be called from outside the class hierarchy, change its visibility to public:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    protected static function baz(): void
+    public static function baz(): void
     {
     }
 }
```

Alternatively, call it from within the class hierarchy by extending the declaring class:

```diff-php
 <?php declare(strict_types = 1);

-class Unrelated
+class Unrelated extends Foo
 {
     public function doFoo(): void
     {
-        Foo::baz();
+        static::baz();
     }
 }
```
