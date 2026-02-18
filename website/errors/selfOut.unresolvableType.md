---
title: "selfOut.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 */
class Foo
{
	/**
	 * @phpstan-self-out self<int&string>
	 */
	public function doFoo(): void
	{
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag contains a type that cannot be resolved. This typically happens when an intersection type is used that produces an impossible type, such as `int&string`, which no value can ever satisfy.

The `@phpstan-self-out` tag is used to narrow the type of `$this` after a method call. If the type is unresolvable, PHPStan cannot determine the resulting type of the object.

## How to fix it

Fix the type in the `@phpstan-self-out` tag to use a valid, resolvable type.

```diff-php
 /**
- * @phpstan-self-out self<int&string>
+ * @phpstan-self-out self<int>
  */
 public function doFoo(): void
 {
 }
```
