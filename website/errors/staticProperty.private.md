---
title: "staticProperty.private"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
    private static int $foo = 1;
}

class ChildClass extends ParentClass
{
    public function doFoo(): int
    {
        return static::$foo;
    }
}
```

## Why is it reported?

The code accesses a private static property of a parent class from a child class. Private properties are only visible within the class that declares them and cannot be accessed from subclasses. This results in a fatal error at runtime.

## How to fix it

Change the property visibility to protected so that child classes can access it:

```diff-php
 <?php declare(strict_types = 1);

 class ParentClass
 {
-    private static int $foo = 1;
+    protected static int $foo = 1;
 }
```

Alternatively, provide a protected or public accessor method in the parent class:

```diff-php
 <?php declare(strict_types = 1);

 class ParentClass
 {
     private static int $foo = 1;
+
+    protected static function getFoo(): int
+    {
+        return self::$foo;
+    }
 }

 class ChildClass extends ParentClass
 {
     public function doFoo(): int
     {
-        return static::$foo;
+        return static::getFoo();
     }
 }
```
