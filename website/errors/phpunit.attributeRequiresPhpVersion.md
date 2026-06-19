---
title: "phpunit.attributeRequiresPhpVersion"
shortDescription: "RequiresPhp attribute has a version requirement that is missing an operator, is invalid, or can never match the analysed PHP version."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\RequiresPhp;

class MyTest extends TestCase
{
	#[RequiresPhp('8.1')]
	public function testFeature(): void
	{
		// ...
	}
}
```

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## Why is it reported?

The `#[RequiresPhp]` attribute restricts a test to certain PHP versions. PHPStan validates the version requirement string and reports several distinct problems under this identifier.

### Version requirement is missing operator

A bare numeric version like `'8.1'` has no comparison operator. PHPUnit 13 and later require an explicit operator, so the bare form is an error:

```php
#[RequiresPhp('8.1')] // error on PHPUnit 13+
```

### Version requirement without operator is deprecated

On PHPUnit 12.4 through 12.x the bare numeric form still works but is deprecated. This message is only reported when [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) is installed:

```php
#[RequiresPhp('8.1')] // deprecated on PHPUnit 12.4+
```

### Version requirement will always evaluate to false

The constraint can never be satisfied by the PHP version PHPStan analyses against (controlled by the [`phpVersion`](/config-reference#phpversion) configuration). For example, requiring an old PHP version while analysing as PHP 8.5:

```php
#[RequiresPhp('< 7.0')] // never true when analysing as PHP 8.x
#[RequiresPhp('^5.0')]  // never true when analysing as PHP 8.x
```

Such a test would always be skipped, which usually indicates a mistake in the constraint.

### Unsupported version constraint

The string cannot be parsed as a version requirement at all. PHPStan reports the underlying parser message, e.g. `Version constraint abc is not supported.`:

```php
#[RequiresPhp('abc')] // not a valid version constraint
```

## How to fix it

Add a comparison operator to a bare numeric version:

```diff-php
-	#[RequiresPhp('8.1')]
+	#[RequiresPhp('>= 8.1')]
 	public function testFeature(): void
```

PHPUnit accepts `version_compare()`-style operators (`>=`, `>`, `<=`, `<`, `=`, `!=`) and Composer-style constraints (`^8.1`, `~8.1`, `8.1.*`):

```diff-php
-	#[RequiresPhp('8.1')]
+	#[RequiresPhp('^8.1')]
 	public function testFeature(): void
```

If the requirement can never match the analysed PHP version, correct the version or the operator so it describes the versions you actually intend to support:

```diff-php
-	#[RequiresPhp('< 7.0')]
+	#[RequiresPhp('>= 8.1')]
 	public function testFeature(): void
```

If an unsupported constraint is reported, replace it with a valid version string. Make sure the [`phpVersion`](/config-reference#phpversion) configuration matches the PHP versions your tests are meant to run on.
