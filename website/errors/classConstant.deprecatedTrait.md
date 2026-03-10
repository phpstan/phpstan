---
title: "classConstant.deprecatedTrait"
shortDescription: "Accessing a class constant on a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
	public const VERSION = '1.0';
}

echo OldHelper::VERSION;
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class constant is accessed directly on a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version, and code should not rely on their constants.

## How to fix it

Access the constant from a non-deprecated source instead:

```diff-php
-echo OldHelper::VERSION;
+echo NewHelper::VERSION;
```
