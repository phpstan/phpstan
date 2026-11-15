---
title: "enum.caseOverrideAttribute"
shortDescription: "The #[Override] attribute is used on an enum case on a PHP version older than 8.6."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasDefault
{
	public const DEFAULT = 'default';
}

enum Mode implements HasDefault
{
	#[\Override] // ERROR: Attribute class Override can be used with enum cases only on PHP 8.6 and later.
	case DEFAULT;
}
```

## Why is it reported?

Support for the `#[\Override]` attribute on class constants and enum cases was added in PHP 8.6. On older PHP versions, the attribute can only target methods (PHP 8.3+) and properties (PHP 8.5+), and using it on an enum case results in an error.

The PHP version is determined by the [`phpVersion`](/config-reference#phpversion) configuration option, or by the PHP version PHPStan runs with.

## How to fix it

If the code needs to run on PHP versions older than 8.6, remove the attribute:

```diff-php
 enum Mode implements HasDefault
 {
-	#[\Override]
 	case DEFAULT;
 }
```

Alternatively, require PHP 8.6 or later and set [`phpVersion`](/config-reference#phpversion) accordingly.
