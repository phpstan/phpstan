---
title: "class.readOnly"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Nonreadonly
{
}

readonly class Foo extends Nonreadonly
{
}
```

## Why is it reported?

A `readonly` class can only extend another `readonly` class, and a non-readonly class cannot extend a `readonly` class. PHP enforces this constraint to maintain consistency of the readonly invariant across the inheritance chain. When a class is declared as `readonly`, all its properties are implicitly `readonly`, and this guarantee must hold for parent classes as well.

In the example above, the readonly class `Foo` extends the non-readonly class `Nonreadonly`, which violates this rule.

## How to fix it

Make the parent class readonly as well:

```diff-php
 <?php declare(strict_types = 1);

-class Nonreadonly
+readonly class Nonreadonly
 {
 }

 readonly class Foo extends Nonreadonly
 {
 }
```

Or remove the `readonly` modifier from the child class:

```diff-php
 <?php declare(strict_types = 1);

 class Nonreadonly
 {
 }

-readonly class Foo extends Nonreadonly
+class Foo extends Nonreadonly
 {
 }
```
