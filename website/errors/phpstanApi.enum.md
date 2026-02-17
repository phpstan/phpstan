---
title: "phpstanApi.enum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Node\Expr\TypeExpr;

class Foo
{
	public function doFoo(object $o): void
	{
		if ($o instanceof TypeExpr) {
			// ...
		}
	}
}
```

## Why is it reported?

The code performs an `instanceof` check against a PHPStan enum that is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). The enum might change in a minor PHPStan version, which could break your code.

PHPStan marks certain types with `@api` to indicate they are safe to use in `instanceof` checks. Enums without this tag are considered internal and may be changed or removed in any minor release.

## How to fix it

Avoid `instanceof` checks against PHPStan enums that are not part of the public API. Use the documented public API methods instead.

If you believe the enum should be covered by the backward compatibility promise, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

Learn more: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
