---
title: "trait.duplicateConstant"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
    public const FOO = 1;
    public const FOO = 2;
}
```

## Why is it reported?

A constant with the same name is declared more than once within the same trait. PHP does not allow redeclaring a constant within the same class-like structure, and this will cause a fatal error at runtime.

Traits gained support for constants in PHP 8.2. Like classes, they cannot contain duplicate constant declarations.

## How to fix it

Remove the duplicate constant declaration and keep only one:

```diff-php
 <?php declare(strict_types = 1);

 trait MyTrait
 {
     public const FOO = 1;
-    public const FOO = 2;
 }
```

If you need different constant values, give them different names:

```diff-php
 <?php declare(strict_types = 1);

 trait MyTrait
 {
     public const FOO = 1;
-    public const FOO = 2;
+    public const BAR = 2;
 }
```
