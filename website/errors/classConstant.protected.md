---
title: "classConstant.protected"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    protected const BAR = 1;
}

echo Foo::BAR;
```

## Why is it reported?

The code accesses a protected class constant from a scope that does not have access to it. Protected constants can only be accessed from within the declaring class or its subclasses. Accessing them from outside the class hierarchy causes a fatal error at runtime.

## How to fix it

Access the constant from within the class hierarchy:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     protected const BAR = 1;

+    public function getBar(): int
+    {
+        return self::BAR;
+    }
 }

-echo Foo::BAR;
+echo (new Foo())->getBar();
```

Or change the constant's visibility to `public` if it should be accessible from outside:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-    protected const BAR = 1;
+    public const BAR = 1;
 }

 echo Foo::BAR;
```
