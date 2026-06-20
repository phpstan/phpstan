---
title: "phpunit.attributeRequiresPhpVersion"
shortDescription: "The PHP version requirement in a RequiresPhp attribute is invalid, incomplete, or always false."
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

The `#[RequiresPhp]` attribute mirrors how PHPUnit parses its version requirement, so PHPStan can warn about requirements that PHPUnit would reject or that can never be satisfied. This identifier covers several distinct problems:

- **Version requirement is missing operator.** A bare version number like `'8.1'` has no comparison operator. Newer PHPUnit versions require an explicit operator (e.g. `>= 8.1`); without one the requirement is ambiguous.
- **Version requirement without operator is deprecated.** On PHPUnit versions where the bare syntax still works but is deprecated, the same `'8.1'` form is reported when [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) is installed.
- **Version requirement is incomplete.** The version is not written in full `major.minor.patch` form (for example `'8.1'` instead of `'8.1.0'`). PHPUnit 12.5+ warns about incomplete versions because they can be interpreted inconsistently. Reported under [bleeding edge](/blog/what-is-bleeding-edge).
- **Version requirement will always evaluate to false.** The constraint can never match any analysed PHP version, so the test would always be skipped.
- The constraint string cannot be parsed at all (e.g. `'abc'`), in which case the underlying parser error message is reported.

## How to fix it

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

Write the version in its full `major.minor.patch` form to avoid the incomplete-version warning:

```diff-php
-	#[RequiresPhp('>= 8.1')]
+	#[RequiresPhp('>= 8.1.0')]
```

If the requirement can never match any PHP version you analyse, correct the operator or version so the constraint is satisfiable, or remove the attribute.
