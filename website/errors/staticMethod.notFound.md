---
title: "staticMethod.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public static function existingMethod(): void {}
}

function doFoo(): void
{
    Foo::nonExistentMethod();
}
```

## Why is it reported?

The code calls a static method that does not exist on the specified class. In PHP, calling a non-existent static method results in a fatal error at runtime unless the class defines the `__callStatic` magic method. PHPStan reports this to catch the error before the code is executed.

## How to fix it

Fix the method name if it was a typo:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-    Foo::nonExistentMethod();
+    Foo::existingMethod();
 }
```

Or add the missing static method to the class:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public static function existingMethod(): void {}
+
+    public static function nonExistentMethod(): void
+    {
+        // ...
+    }
 }
```

If the class uses `__callStatic` to handle dynamic static method calls, PHPStan may need to be informed about the available methods via a `@method` PHPDoc tag on the class:

```diff-php
 <?php declare(strict_types = 1);

+/**
+ * @method static void nonExistentMethod()
+ */
 class Foo
 {
     public static function __callStatic(string $name, array $arguments): mixed
     {
         // ...
     }
 }
```
