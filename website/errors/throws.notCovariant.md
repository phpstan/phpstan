---
title: "throws.notCovariant"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @throws \LogicException
	 */
	public function doSomething(): void
	{
	}
}

class Bar extends Foo
{
	/**
	 * @throws \RuntimeException
	 */
	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

The `@throws` type declared on an overriding method is not covariant with the `@throws` type of the parent method. Covariance means the child's throw type must be the same as or a subtype of the parent's throw type.

In the example above, the parent declares `@throws \LogicException`, but the child declares `@throws \RuntimeException`. Since `RuntimeException` does not extend `LogicException`, this violates the covariance principle. Callers that catch `LogicException` based on the parent's contract would not catch `RuntimeException`.

This rule is controlled by the [`exceptions.check.throwTypeCovariance`](/config-reference#exceptions.check.throwTypeCovariance) configuration option.

## How to fix it

Change the child's `@throws` tag to a subtype of the parent's throw type:

```diff-php
 class Bar extends Foo
 {
 	/**
-	 * @throws \RuntimeException
+	 * @throws \BadFunctionCallException
 	 */
 	public function doSomething(): void
 	{
 	}
 }
```

`BadFunctionCallException` is a subtype of `LogicException`, so it satisfies the covariance requirement.

Alternatively, if the method truly does not throw, use `@throws void`:

```diff-php
 class Bar extends Foo
 {
 	/**
-	 * @throws \RuntimeException
+	 * @throws void
 	 */
 	public function doSomething(): void
 	{
 	}
 }
```
