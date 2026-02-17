---
title: "phpstanApi.method"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Analyser\Scope;

class MyRule
{
	public function process(Scope $scope): void
	{
		$scope->someInternalMethod();
	}
}
```

## Why is it reported?

A method is being called on a PHPStan class that is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise). Only methods on classes and interfaces marked with the `@api` PHPDoc tag are considered part of the stable public API. Methods not covered by this promise might change or be removed in minor PHPStan versions without notice.

If you think the method should be covered by the backward compatibility promise, you can [open a discussion](https://github.com/phpstan/phpstan/discussions).

## How to fix it

Use only methods that are part of PHPStan's public API (those on `@api`-tagged classes and interfaces). Check the PHPStan documentation or source code for the intended public API alternatives.

```diff-php
 <?php declare(strict_types = 1);

 use PHPStan\Analyser\Scope;

 class MyRule
 {
 	public function process(Scope $scope): void
 	{
-		$scope->someInternalMethod();
+		$scope->getType($expr);
 	}
 }
```
