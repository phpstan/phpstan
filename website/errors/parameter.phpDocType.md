---
title: "parameter.phpDocType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @param string $value
	 */
	public function doFoo(int $value): void
	{
	}
}
```

## Why is it reported?

The PHPDoc `@param` tag specifies a type that is incompatible with the native parameter type declaration. PHPDoc types for parameters must be narrower than (a subtype of) the native type, not wider or completely different.

In the example above, the native type is `int` but the PHPDoc says `string`. These types are incompatible because `string` is not a subtype of `int`.

A valid use of `@param` is to narrow a native type, for example specifying `@param positive-int $value` for a parameter typed as `int`, or `@param non-empty-string $name` for a `string` parameter.

## How to fix it

Make the PHPDoc type compatible with the native type. The PHPDoc type should be equal to or narrower than the native type:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/**
-	 * @param string $value
+	 * @param positive-int $value
 	 */
 	public function doFoo(int $value): void
 	{
 	}
 }
```

Or remove the PHPDoc tag if it does not add useful type information beyond the native declaration:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	/**
-	 * @param string $value
-	 */
 	public function doFoo(int $value): void
 	{
 	}
 }
```
