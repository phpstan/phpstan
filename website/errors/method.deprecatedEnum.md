---
title: "method.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';

	public function label(): string
	{
		return $this->value;
	}
}

function doFoo(OldStatus $status): void
{
	$status->label(); // ERROR: Call to method label() of deprecated enum OldStatus.
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

A method is being called on an instance of an enum that has been marked as `@deprecated`. Even though the method itself is not deprecated, the entire enum is deprecated, which means all usage of the enum -- including calling its methods -- should be replaced with the suggested alternative.

## How to fix it

Replace the usage of the deprecated enum with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(OldStatus $status): void
+function doFoo(NewStatus $status): void
 {
 	$status->label();
 }
```
