---
title: "propertySetHook.parameterType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $value {
		/** @param positive-int $v */
		set (int $v) { // ERROR: Type int<1, max> of set hook parameter $v is not contravariant with type int of property Foo::$value.
		}
	}
}
```

## Why is it reported?

In PHP 8.4 property hooks, the type of the set hook's parameter must be contravariant with (i.e. the same as or wider than) the property type. This rule applies to PHPDoc types as well as native types.

In the example above, the set hook parameter has a PHPDoc type of `positive-int` (`int<1, max>`), which is narrower than the property's `int` type. This means the set hook would reject some values that the property type allows, creating an inconsistency.

## How to fix it

Make the set hook parameter type at least as wide as the property type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public int $value {
-		/** @param positive-int $v */
 		set (int $v) {
 		}
 	}
 }
```

Alternatively, narrow the property type to match the parameter constraint:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
+	/** @var positive-int */
 	public int $value {
 		/** @param positive-int $v */
 		set (int $v) {
 		}
 	}
 }
```
