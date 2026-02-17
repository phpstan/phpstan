---
title: "phpstanApi.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// When a PHPStan extension uses a PHPStan internal trait:

use PHPStan\SomeInternalTrait;

class MyExtension
{
	use SomeInternalTrait;
}
```

## Why is it reported?

The code uses a PHPStan trait that is not marked with the `@api` tag. Traits without the `@api` tag are internal to PHPStan and are not covered by the backward compatibility promise. They may change or be removed in minor PHPStan versions without notice.

## How to fix it

Use only `@api`-tagged PHPStan types in your extensions. If you believe the trait should be part of the public API, you can open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

See also: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
