---
title: "classConstant.notFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public const BAR = 1;
}

echo Foo::BAZ;
```

## Why is it reported?

The code accesses a class constant that does not exist on the specified class. This will cause a fatal error at runtime. This is often caused by a typo in the constant name or by accessing a constant that was removed or renamed.

## How to fix it

Use the correct constant name:

```diff-php
 <?php declare(strict_types = 1);

-echo Foo::BAZ;
+echo Foo::BAR;
```

Or define the missing constant:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public const BAR = 1;
+    public const BAZ = 2;
 }

 echo Foo::BAZ;
```
