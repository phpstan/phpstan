---
title: "phpstanApi.classConstant"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Analyser\NodeScopeResolver;

class Foo
{
	public function doFoo(): void
	{
		echo NodeScopeResolver::FOO;
	}
}
```

## Why is it reported?

The code accesses a class constant (including `::class`) on a PHPStan class that is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). The class might change in a minor PHPStan version, which could break your code.

PHPStan marks certain classes with `@api` to indicate their constants are safe to access. Classes without this tag are considered internal and may have their constants changed or removed in any minor release.

## How to fix it

Use only class constants from classes that are covered by the backward compatibility promise (marked with `@api`). Check the PHPStan source code to see if the class whose constants you want to access is part of the public API.

If you believe the class should be covered by the backward compatibility promise, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

Learn more: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
