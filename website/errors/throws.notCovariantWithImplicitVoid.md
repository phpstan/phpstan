---
title: "throws.notCovariantWithImplicitVoid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public function process(): void
	{
	}
}

class ChildClass extends ParentClass
{
	/**
	 * @throws \RuntimeException
	 */
	public function process(): void
	{
		throw new \RuntimeException('Error.');
	}
}
```

## Why is it reported?

A child method declares a `@throws` type, but the parent method does not have a `@throws` PHPDoc tag. When the [`exceptions.implicitThrows`](/config-reference#exceptions.implicitthrows) config parameter is set to `false`, a missing `@throws` tag is treated the same as `@throws void`, meaning the method promises not to throw any exceptions. A child method that declares exceptions in its `@throws` tag violates this contract.

This rule enforces that `@throws` types follow the same covariance rules as other type declarations: a child method cannot throw more exception types than its parent allows.

## How to fix it

Remove the `@throws` tag from the child method if the parent does not allow throwing exceptions:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	/**
-	 * @throws \RuntimeException
-	 */
 	public function process(): void
 	{
-		throw new \RuntimeException('Error.');
 	}
 }
```

Or add a `@throws` tag to the parent method to explicitly allow exceptions:

```diff-php
 <?php declare(strict_types = 1);

 class ParentClass
 {
+	/**
+	 * @throws \RuntimeException
+	 */
 	public function process(): void
 	{
 	}
 }
```
