---
title: "phpstanApi.classConstant"
shortDescription: "Accessing a class constant on a PHPStan class not covered by the BC promise."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Command\AnalyseCommand;
use PHPStan\Turbo\TurboExtensionSelector;

class Foo
{
	public function doFoo(): void
	{
		echo AnalyseCommand::class;
		echo TurboExtensionSelector::MINIMUM_PHP_VERSION_ID;
	}
}
```

## Why is it reported?

The code accesses a class constant (including `::class`) declared in a PHPStan class that is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). Such a class or constant might change or be removed in a minor PHPStan version, which could break your code.

PHPStan uses the `@api` PHPDoc tag to mark what is safe to use. Accessing a class constant is allowed when:

- the declaring class is marked with `@api`,
- the constant itself is marked with `@api`,
- or, for `Foo::class`, at least one method of the class is marked with `@api`.

All other constants on PHPStan classes are considered internal.

## How to fix it

Use only class constants that are covered by the backward compatibility promise. Check the PHPStan source code to see whether the class or the constant is marked with `@api`:

```diff-php
 use PHPStan\Turbo\TurboExtensionSelector;

-echo TurboExtensionSelector::MINIMUM_PHP_VERSION_ID;
+echo TurboExtensionSelector::EXTENSION_PATH_INI; // marked with @api
```

If the value is needed, consider defining your own constant instead of relying on an internal one.

If you believe the class or constant should be covered by the backward compatibility promise, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

Learn more: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
