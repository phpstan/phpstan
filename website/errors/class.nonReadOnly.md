---
title: "class.nonReadOnly"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

readonly class ParentClass
{
}

class ChildClass extends ParentClass
{
}
```

## Why is it reported?

A non-readonly class cannot extend a `readonly` class. PHP enforces this constraint to maintain the readonly invariant across the inheritance chain. When a class is declared as `readonly`, all its properties are implicitly `readonly`, and this guarantee must hold for all subclasses as well. Allowing a non-readonly child class would break this invariant because the child could introduce mutable properties.

In the example above, `ChildClass` is not readonly but extends the readonly `ParentClass`, which violates this rule.

## How to fix it

Make the child class readonly as well:

```diff-php
 <?php declare(strict_types = 1);

 readonly class ParentClass
 {
 }

-class ChildClass extends ParentClass
+readonly class ChildClass extends ParentClass
 {
 }
```

Or remove the `readonly` modifier from the parent class if mutability is needed:

```diff-php
 <?php declare(strict_types = 1);

-readonly class ParentClass
+class ParentClass
 {
 }

 class ChildClass extends ParentClass
 {
 }
```
