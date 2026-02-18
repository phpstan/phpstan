---
title: "missingType.callable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(callable $cb): void // error: Method Foo::doFoo() has parameter $cb
	{                                         //        with no signature specified for callable.
	}
}
```

## Why is it reported?

Using `callable` without specifying its parameter and return types means PHPStan cannot verify that the correct callable is being passed. A bare `callable` type is equivalent to accepting any callable regardless of its signature, which can lead to runtime type errors when the callable is invoked with unexpected arguments or returns an unexpected type.

This error is reported when the [`checkMissingCallableSignature`](/config-reference#checkmissingcallablesignature) option is enabled.

## How to fix it

Specify the callable signature using PHPDoc.

```diff-php
 class Foo
 {
+	/**
+	 * @param callable(string): bool $cb
+	 */
 	public function doFoo(callable $cb): void
 	{
 	}
 }
```

Alternatively, use a `Closure` type with a specified signature.

```diff-php
 class Foo
 {
-	public function doFoo(callable $cb): void
+	/**
+	 * @param \Closure(string): bool $cb
+	 */
+	public function doFoo(\Closure $cb): void
 	{
 	}
 }
```
