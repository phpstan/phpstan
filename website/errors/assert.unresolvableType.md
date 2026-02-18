---
title: "assert.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Validator
{
	/**
	 * @phpstan-assert string&int $value
	 */
	public function assertValid(mixed $value): void
	{
	}
}
```

## Why is it reported?

The type used in the `@phpstan-assert` tag cannot be resolved to a valid type. This usually happens when the asserted type is an impossible intersection (like `string&int` which can never exist), references an undefined class, or contains a syntax error.

In the example above, `string&int` is an intersection type that can never be satisfied because no value can be both a `string` and an `int` at the same time.

## How to fix it

Use a valid, resolvable type in the assertion:

```diff-php
 /**
- * @phpstan-assert string&int $value
+ * @phpstan-assert string $value
  */
 public function assertValid(mixed $value): void
 {
 }
```

If the intent is to assert a union type, use `|` instead of `&`:

```diff-php
- * @phpstan-assert string&int $value
+ * @phpstan-assert string|int $value
```
