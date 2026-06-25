---
title: "phpunit.attributeRequiresPhpVersion"
shortDescription: "RequiresPhp attribute has an invalid, incomplete, or unsatisfiable PHP version requirement."
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

The `#[RequiresPhp]` attribute is also checked when placed on the test class itself, not just on individual test methods:

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\RequiresPhp;

#[RequiresPhp('< 7.0')]
class MyTest extends TestCase
{
	public function testFeature(): void
	{
		// ...
	}
}
```

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## Why is it reported?

The `#[RequiresPhp]` attribute controls whether a test (or every test in a class) runs on the analysed PHP version. PHPStan reports several problems with its version requirement:

- **Missing operator** — a bare version number like `'8.1'` is given without a comparison operator. In newer PHPUnit versions the requirement must include an explicit operator (e.g. `>= 8.1`); a bare numeric version is ambiguous. On older PHPUnit versions where it is merely deprecated, the error is reported only when [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) is installed.
- **Always false** — the constraint can never match any analysed PHP version (e.g. `'< 7.0'` while analysing PHP 8.x), so the test would always be skipped.
- **Incomplete version** — the version is not a full `major.minor.patch` triple. PHPUnit may interpret it in surprising ways. This warning is reported on [bleeding edge](/blog/what-is-bleeding-edge).
- **Invalid constraint** — the version string is not a valid version constraint at all.

## How to fix it

Use a full `major.minor.patch` version together with an operator that can actually be satisfied by the analysed PHP version.

Add a comparison operator to the version requirement:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;
 use PHPUnit\Framework\Attributes\RequiresPhp;

 class MyTest extends TestCase
 {
-	#[RequiresPhp('8.1')]
+	#[RequiresPhp('>= 8.1')]
 	public function testFeature(): void
 	{
 		// ...
 	}
 }
```
