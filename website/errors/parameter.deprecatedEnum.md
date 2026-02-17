---
title: "parameter.deprecatedEnum"
ignorable: true
---

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum OldStatus: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

function processOrder(OldStatus $status): void
{
}
```

## Why is it reported?

A function or method parameter uses a deprecated enum as its native type declaration. The enum has been marked with a `@deprecated` PHPDoc tag, indicating it should no longer be used. Using a deprecated enum in a parameter type ties new code to an obsolete API.

## How to fix it

Replace the deprecated enum with its recommended replacement in the parameter type declaration:

```diff-php
 <?php declare(strict_types = 1);

-function processOrder(OldStatus $status): void
+function processOrder(NewStatus $status): void
 {
 }
```
