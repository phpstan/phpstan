---
title: "interfaceExtends.trait"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
}

interface MyInterface extends MyTrait
{
}
```

## Why is it reported?

An interface declares that it extends a trait, which is not allowed in PHP. Interfaces can only extend other interfaces. Traits are a different kind of language construct used for horizontal code reuse via `use` statements in classes, not for interface inheritance.

## How to fix it

If the target should be an interface, change the trait to an interface:

```diff-php
-trait MyTrait
+interface MyTrait
 {
 }

 interface MyInterface extends MyTrait
 {
 }
```

If the trait is correct, the interface should not extend it. Instead, a class can use the trait and implement the interface separately:

```diff-php
 trait MyTrait
 {
 }

-interface MyInterface extends MyTrait
+interface MyInterface
 {
 }
+
+class MyClass implements MyInterface
+{
+	use MyTrait;
+}
```
