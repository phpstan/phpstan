---
title: "selfOut.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewTrait instead */
trait OldTrait
{
}

/**
 * @template T
 */
class Collection
{
	/**
	 * @phpstan-self-out self<OldTrait>
	 */
	public function withTrait(): void
	{
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a deprecated trait. Using deprecated symbols propagates reliance on code that is scheduled for removal or replacement.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated trait reference with its non-deprecated replacement.

```diff-php
 /**
- * @phpstan-self-out self<OldTrait>
+ * @phpstan-self-out self<NewTrait>
  */
 public function withTrait(): void
 {
 }
```
