---
title: "staticMethod.nonObject"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param int|string $value
 */
function doFoo($value): void
{
    $value::foo();
}
```

## Why is it reported?

A static method is being called on a value that is not an object or class name. Static methods can only be called on class names (as strings or class references) or on objects. Calling a static method on a type like `int`, `float`, `bool`, or a union that does not guarantee an object type will result in a runtime error.

## How to fix it

Ensure the variable holds a valid class name or object before calling a static method on it:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @param int|string $value
- */
-function doFoo($value): void
+function doFoo(string $className): void
 {
-    $value::foo();
+    if (is_a($className, MyClass::class, true)) {
+        $className::foo();
+    }
 }
```

Or call the static method directly on the class:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @param int|string $value
- */
-function doFoo($value): void
+function doFoo(): void
 {
-    $value::foo();
+    MyClass::foo();
 }
```
