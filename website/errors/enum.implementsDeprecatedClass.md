---
title: "enum.implementsDeprecatedClass"
shortDescription: "Enum implements a deprecated class."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @deprecated */
class OldService
{
}

enum Status implements OldService
{
	case Active;
}
```

## Why is it reported?

The enum implements a class that is marked as `@deprecated`. Internal classes are not intended to be implemented, and using deprecated types couples new code to APIs that are scheduled for removal. This rule is provided by [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules).

Note: triggering this identifier requires an enum to implement a class (not an interface), which PHP itself forbids. PHPStan therefore always also reports a non-ignorable `enumImplements.class` error alongside this one.

## How to fix it

Remove the deprecated class from the `implements` clause and use a proper public interface instead:

```diff-php
-enum Status implements OldService
+enum Status implements PublicInterface
 {
 	case Active;
 }
```
