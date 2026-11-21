---
title: "classConstant.override"
shortDescription: "Class constant has the #[Override] attribute but does not override any constant."
ignorable: false
autofixable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public const FOO = 1;
}

class Child extends Base
{
	#[\Override]
	public const FOO = 2;

	#[\Override]
	public const BAR = 3; // ERROR: Constant Child::BAR has #[\Override] attribute but does not override any constant.
}
```

## Why is it reported?

The `#[\Override]` attribute on a class constant (PHP 8.6+) declares that the constant overrides a constant from a parent class or an implemented interface. If no parent class or interface declares a constant with the same name, the attribute is incorrect and PHP throws a fatal error.

This typically happens when the constant in the parent class was renamed or removed, or when the overriding constant has a typo in its name. Private constants of the parent class cannot be overridden, so they do not count either.

## How to fix it

If the constant is not meant to override anything, remove the attribute:

```diff-php
 class Child extends Base
 {
 	#[\Override]
 	public const FOO = 2;

-	#[\Override]
 	public const BAR = 3;
 }
```

If the constant is supposed to override a parent constant, fix the name so it matches:

```diff-php
 class Child extends Base
 {
 	#[\Override]
-	public const FOOO = 2;
+	public const FOO = 2;
 }
```
