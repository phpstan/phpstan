---
title: "phpstanApi.trait"
shortDescription: "Using a PHPStan trait not covered by the backward compatibility promise."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use PHPStan\Type\JustNullableTypeTrait;

class Foo
{
	use JustNullableTypeTrait;
}
```

## Why is it reported?

The code uses a PHPStan trait that is not marked with the `@api` tag. Traits without the `@api` tag are internal to PHPStan and are not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). They may change or be removed in minor PHPStan versions without notice, which could break your extension.

Traits marked with `@api` (for example `PHPStan\Type\Traits\LateResolvableTypeTrait`) are covered by the backward compatibility promise and can be used safely.

## How to fix it

Use only `@api`-tagged PHPStan traits in your extensions, or implement the needed functionality in your own code instead of relying on an internal trait.

```diff-php
 class Foo
 {
-	use \PHPStan\Type\JustNullableTypeTrait;
+	// implement the needed methods directly
 }
```

If you believe the trait should be part of the public API, you can open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

See also: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
