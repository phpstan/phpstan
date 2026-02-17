---
title: "class.duplicateConstant"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public const BAR = 1;
    public const BAR = 2;
}
```

## Why is it reported?

The class declares the same constant name more than once. PHP does not allow redeclaring a class constant within the same class body. This will cause a fatal error at runtime.

## How to fix it

Remove the duplicate constant declaration, or rename one of the constants to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public const BAR = 1;
-    public const BAR = 2;
+    public const BAZ = 2;
 }
```
