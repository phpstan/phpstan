---
title: "phpstanApi.interface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Analyser\Scope;

class MyScope implements Scope
{
	// ...
}
```

## Why is it reported?

The code implements or extends a PHPStan interface that is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). The interface might change in a minor PHPStan version, which could break your implementation.

PHPStan marks certain interfaces with `@api` to indicate they are safe to implement. Interfaces without this tag, or those marked with `@api-do-not-implement`, are considered internal and may have methods added or changed in any minor release.

## How to fix it

Use only interfaces that are covered by the backward compatibility promise (marked with `@api`). Check the PHPStan source code to see if the interface you want to implement is part of the public API.

If you believe the interface should be covered by the backward compatibility promise, open a discussion at [https://github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

Learn more: [Backward Compatibility Promise](/developing-extensions/backward-compatibility-promise)
