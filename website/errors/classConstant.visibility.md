---
title: "classConstant.visibility"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    public const BAR = 1;
}

class Bar extends Foo
{
    private const BAR = 2;
}
```

## Why is it reported?

A class constant overrides a parent class or trait constant with a more restrictive visibility. PHP requires that an overriding constant must have the same or less restrictive visibility than the constant it overrides. For example, a `public` constant cannot be overridden as `protected` or `private`, and a `protected` constant cannot be overridden as `private`. Violating this rule causes a fatal error at runtime.

## How to fix it

Match or widen the visibility to be compatible with the parent:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     public const BAR = 1;
 }

 class Bar extends Foo
 {
-    private const BAR = 2;
+    public const BAR = 2;
 }
```
