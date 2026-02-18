---
title: "selfOut.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated */
enum Status: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

class Builder
{
	/**
	 * @phpstan-self-out self<Status>
	 */
	public function withStatus(): void
	{
	}
}
```

## Why is it reported?

The `@phpstan-self-out` PHPDoc tag references a deprecated enum. Using deprecated symbols propagates reliance on code that is scheduled for removal or replacement.

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated enum with its non-deprecated replacement.

```diff-php
 class Builder
 {
 	/**
-	 * @phpstan-self-out self<Status>
+	 * @phpstan-self-out self<NewStatus>
 	 */
 	public function withStatus(): void
 	{
 	}
 }
```
