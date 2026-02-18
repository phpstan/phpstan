---
title: "phpstanApi.phpstanNamespace"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace PHPStan\Custom;

class MyType
{
}
```

## Why is it reported?

A third-party package declares classes in the `PHPStan\` namespace. This namespace is reserved for PHPStan itself. Declaring classes in this namespace from outside the `phpstan/*` packages can cause conflicts and is not covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise).

## How to fix it

Move the class to your own namespace:

```diff-php
-namespace PHPStan\Custom;
+namespace MyVendor\PHPStanExtension;

 class MyType
 {
 }
```

Learn more: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
