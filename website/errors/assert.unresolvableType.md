---
title: "assert.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/** @var string|null */
	public $fooProp;

	/**
	 * @phpstan-assert string&int $a
	 */
	public function doFoo($a): bool
	{
		return true;
	}
}
```

## Why is it reported?

The type used in the `@phpstan-assert` tag cannot be resolved to a valid type. This usually happens when the asserted type is an impossible intersection (like `string&int` which can never exist), references an undefined class, or contains a syntax error. PHPStan cannot determine what the assertion means, so it reports an error.

In the example above, `string&int` is an intersection type that can never be satisfied because no value can be both a `string` and an `int` at the same time.

## How to fix it

Use a valid, resolvable type in the assertion:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/** @var string|null */
 	public $fooProp;

 	/**
-	 * @phpstan-assert string&int $a
+	 * @phpstan-assert string $a
 	 */
 	public function doFoo($a): bool
 	{
 		return true;
 	}
 }
```

If the intent is to assert a union type, use `|` instead of `&`:

```diff-php
-	 * @phpstan-assert string&int $a
+	 * @phpstan-assert string|int $a
```
