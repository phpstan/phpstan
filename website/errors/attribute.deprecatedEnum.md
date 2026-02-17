---
title: "attribute.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus
{
	case Active;
	case Inactive;
}

#[\Attribute]
class StatusAttribute
{
	public function __construct(public string $status)
	{
	}
}

// Somewhere using OldStatus in an attribute context:
// Attribute references deprecated enum OldStatus.
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

An attribute references an enum that has been marked as `@deprecated`. Deprecated enums are planned for removal in a future version, and attributes should not rely on them.

## How to fix it

Replace the usage of the deprecated enum with its recommended replacement.
