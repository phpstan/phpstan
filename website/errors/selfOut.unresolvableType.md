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
class Collection
{
	/**
	 * @phpstan-self-out self<int&string>
	 */
	public function filterIntegers(): void
	{
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag contains a type that cannot be resolved. This typically happens when the type includes an impossible intersection (such as `int&string`, which no value can ever satisfy), references an undefined class, or uses invalid type syntax.

The `@phpstan-self-out` tag is used to narrow the type of `$this` after a method call. If the type is unresolvable, PHPStan cannot determine the resulting type of the object.

In the example above, `self<int&string>` contains the impossible intersection `int&string`, making the entire type unresolvable.

## How to fix it

Use a valid, resolvable type in the `@phpstan-self-out` tag:

```diff-php
 /**
- * @phpstan-self-out self<int&string>
+ * @phpstan-self-out self<int>
  */
 public function filterIntegers(): void
 {
 }
```
