---
title: "selfOut.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

class MyClass
{
    /**
     * @phpstan-self-out self&MyTrait
     */
    public function apply(): void
    {
    }
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a trait. Traits cannot be used in type declarations or type operations because they are not types in PHP's type system. They are a code reuse mechanism and cannot appear in `instanceof` checks, type hints, or intersection types at runtime. Using a trait in `@phpstan-self-out` does not produce a meaningful type.

## How to fix it

Replace the trait with an interface that describes the contract:

```diff-php
-trait MyTrait
+interface MyInterface
 {
 }

 class MyClass
 {
     /**
-     * @phpstan-self-out self&MyTrait
+     * @phpstan-self-out self&MyInterface
      */
     public function apply(): void
     {
     }
 }
```
