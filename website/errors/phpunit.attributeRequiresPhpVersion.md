---
title: "phpunit.attributeRequiresPhpVersion"
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

The `#[RequiresPhp]` attribute specifies a bare version number like `'8.1'` without a comparison operator. Depending on the PHPUnit version, this is either required to include an operator or the bare version syntax is deprecated.

In newer PHPUnit versions, the version requirement must include an explicit comparison operator (e.g. `>= 8.1`). Passing only a numeric version string is ambiguous and may not behave as expected.

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
