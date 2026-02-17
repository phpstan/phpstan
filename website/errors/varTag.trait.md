---
title: "varTag.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait FooTrait
{
}

class Foo
{
    public function doFoo(): void
    {
        /** @var FooTrait $test */
        $test = new self();
    }
}
```

## Why is it reported?

The `@var` PHPDoc tag references a trait as a type. Traits cannot be used as types in PHP because they are not instantiable and cannot be used in `instanceof` checks or type declarations. A variable cannot hold a value "of type trait" -- traits are only used via the `use` keyword inside classes.

## How to fix it

Use an interface or a class instead of a trait in the `@var` tag:

```diff-php
 <?php declare(strict_types = 1);

+interface FooInterface
+{
+}
+
 trait FooTrait
 {
 }

 class Foo
 {
     public function doFoo(): void
     {
-        /** @var FooTrait $test */
+        /** @var FooInterface $test */
         $test = new self();
     }
 }
```

Or use the concrete class type that uses the trait:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
     use FooTrait;

     public function doFoo(): void
     {
-        /** @var FooTrait $test */
+        /** @var Foo $test */
         $test = new self();
     }
 }
```
