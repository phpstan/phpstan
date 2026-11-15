---
title: "enum.caseType"
shortDescription: "Enum case value does not match the declared backing type, or the case is incompatible with the type of the overridden interface constant."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status: int
{
	case Active = 'active';
	case Inactive = 2;
}
```

This error is also reported when an enum case overrides an interface constant whose PHPDoc type does not accept the enum case:

```php
<?php declare(strict_types = 1);

interface PhpDocTyped
{
	/** @var int */
	public const INT = 1;
}

enum Foo implements PhpDocTyped
{
	case INT; // ERROR: Enum case Foo::INT is not compatible with type int of constant PhpDocTyped::INT.
}
```

## Why is it reported?

A backed enum declares the type of its case values in the enum declaration (`int` in this example). Every case value must match that type. The value `'active'` is a string, which does not match the declared `int` backing type. PHP will throw a compile-time error for this mismatch.

An enum case is also a class constant of the enum. When the enum implements an interface that declares a constant with the same name, the case overrides that constant. The value of the case is the enum case object itself (not its backing value), so if the interface constant is documented with a `@var` type that does not accept the enum case, code reading the constant through the interface receives an unexpected value.

## How to fix it

Change the case value to match the declared backing type:

```diff-php
 enum Status: int
 {
-	case Active = 'active';
+	case Active = 1;
 	case Inactive = 2;
 }
```

Alternatively, change the backing type if string values are intended:

```diff-php
-enum Status: int
+enum Status: string
 {
 	case Active = 'active';
-	case Inactive = 2;
+	case Inactive = 'inactive';
 }
```

When the case overrides an interface constant, rename the case so it no longer collides with the constant:

```diff-php
 enum Foo implements PhpDocTyped
 {
-	case INT;
+	case Int;
 }
```

Or widen the PHPDoc type of the interface constant so that it accepts the enum case:

```diff-php
 interface PhpDocTyped
 {
-	/** @var int */
+	/** @var int|\UnitEnum */
 	public const INT = 1;
 }
```
