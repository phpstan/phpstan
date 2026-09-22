---
title: "phpunit.attributeRequiresPhpVersion"
shortDescription: "RequiresPhp or RequiresPhpunit attribute has an invalid, incomplete, or unsatisfiable version requirement."
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

The same checks apply to the `#[RequiresPhpunit]` attribute, which restricts the PHPUnit version a test runs on:

```php
<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\RequiresPhpunit;

class MyTest extends TestCase
{
	#[RequiresPhpunit('11.0')]
	public function testFeature(): void
	{
		// ...
	}
}
```

This rule is provided by the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) extension.

## Why is it reported?

The `#[RequiresPhp]` and `#[RequiresPhpunit]` attributes control whether a test (or every test in a class) runs on the current PHP or PHPUnit version. PHPStan reports several problems with their version requirement:

- **Missing operator** — a bare version number like `'8.1'` is given without a comparison operator. In newer PHPUnit versions the requirement must include an explicit operator (e.g. `>= 8.1`); a bare numeric version is ambiguous. On older PHPUnit versions where it is merely deprecated, the error is reported only when [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) is installed.
- **Always false** — the constraint can never match any analysed PHP version (e.g. `'< 7.0'` while analysing PHP 8.x), so the test would always be skipped. The analysed PHP versions come from the [`phpVersion`](/config-reference#phpversion) setting or from `composer.json`. For `#[RequiresPhpunit]`, the constraint is compared with the PHPUnit versions allowed by `composer.json`. This check runs on [bleeding edge](/blog/what-is-bleeding-edge).
- **Incomplete version** — the version is not a full `major.minor.patch` triple. PHPUnit may interpret it in surprising ways. This warning is reported on [bleeding edge](/blog/what-is-bleeding-edge).
- **Invalid constraint** — the version string is not a valid version constraint at all. This check runs on [bleeding edge](/blog/what-is-bleeding-edge).

## How to fix it

Use a full `major.minor.patch` version together with an operator that can actually be satisfied by the analysed PHP (or PHPUnit) version.

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

Use a complete `major.minor.patch` version:

```diff-php
 <?php declare(strict_types = 1);

 use PHPUnit\Framework\TestCase;
 use PHPUnit\Framework\Attributes\RequiresPhpunit;

 class MyTest extends TestCase
 {
-	#[RequiresPhpunit('11.0')]
+	#[RequiresPhpunit('>= 11.0.0')]
 	public function testFeature(): void
 	{
 		// ...
 	}
 }
```
