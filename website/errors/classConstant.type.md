---
title: "classConstant.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	/** @var positive-int */
	public const VALUE = 1;
}

class ChildClass extends ParentClass
{
	/** @var int */
	public const VALUE = -1;
}
```

## Why is it reported?

The type of an overriding class constant is not covariant with the type of the constant in the parent class. When a child class overrides a constant from a parent class, the child's constant type must be a subtype of (or equal to) the parent's constant type. This ensures that code relying on the parent's type contract remains valid.

In the example above, `ParentClass::VALUE` is typed as `positive-int`, but `ChildClass::VALUE` widens the type to `int` and assigns `-1`, which violates the parent's type contract.

## How to fix it

Ensure the overriding constant's type is a subtype of the parent's type:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	/** @var int */
-	public const VALUE = -1;
+	/** @var positive-int */
+	public const VALUE = 2;
 }
```
