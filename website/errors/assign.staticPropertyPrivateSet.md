---
title: "assign.staticPropertyPrivateSet"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public private(set) static int $count = 0;
}

Foo::$count = 5;
```

## Why is it reported?

The static property uses asymmetric visibility with `private(set)`, meaning it can be read publicly but can only be written to from within the declaring class. The code is attempting to assign a value to this property from outside the class, which violates the write visibility restriction.

Asymmetric visibility for static properties is a PHP 8.4+ feature that allows separate read and write access levels.

## How to fix it

Use a public method on the class to modify the property:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public private(set) static int $count = 0;

+    public static function setCount(int $value): void
+    {
+        self::$count = $value;
+    }
 }

-Foo::$count = 5;
+Foo::setCount(5);
```

Or change the property's write visibility if external writes are intended:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    public private(set) static int $count = 0;
+    public static int $count = 0;
 }

 Foo::$count = 5;
```
