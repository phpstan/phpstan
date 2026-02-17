---
title: "classConstant.nonFinal"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait HasVersion
{
    final public const VERSION = '1.0';
}

class App
{
    use HasVersion;

    public const VERSION = '1.0';
}
```

## Why is it reported?

The class declares a constant that overrides a `final` constant from a used trait, but does not declare it as `final` as well. When a trait defines a constant as `final`, any class that uses the trait and redeclares that constant must also declare it as `final`. This is a PHP language constraint for trait constant inheritance.

## How to fix it

Add the `final` keyword to the overriding constant:

```diff-php
 <?php declare(strict_types = 1);

 class App
 {
     use HasVersion;

-    public const VERSION = '1.0';
+    final public const VERSION = '1.0';
 }
```

Or remove the redeclaration and let the trait constant be inherited:

```diff-php
 <?php declare(strict_types = 1);

 class App
 {
     use HasVersion;
-
-    public const VERSION = '1.0';
 }
```
