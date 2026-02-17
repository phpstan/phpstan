---
title: "sealed.onTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed AllowedClass
 */
trait MyTrait
{
    public function doSomething(): void
    {
    }
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag is placed on a trait. This tag is only valid on classes and interfaces, where it restricts which types are allowed to extend or implement them. Traits cannot be extended or implemented directly -- they are included via `use` statements -- so the `@phpstan-sealed` tag has no meaning on a trait.

## How to fix it

Remove the `@phpstan-sealed` tag from the trait:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-sealed AllowedClass
- */
 trait MyTrait
 {
     public function doSomething(): void
     {
     }
 }
```

If the goal is to restrict which classes can use this trait, consider converting the trait to an abstract class or an interface with a `@phpstan-sealed` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-sealed AllowedClass
  */
-trait MyTrait
+interface MyInterface
 {
-    public function doSomething(): void
-    {
-    }
+    public function doSomething(): void;
 }
```
