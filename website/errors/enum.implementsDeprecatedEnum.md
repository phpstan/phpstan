---
title: "enum.implementsDeprecatedEnum"
shortDescription: "Enum implements a deprecated enum."
ignorable: true
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @deprecated */
enum OldStatus
{
	case Active;
}

enum Color implements OldStatus
{
	case Red;
}
```

## Why is it reported?

The enum implements another enum that is marked as `@deprecated`. Implementing deprecated types couples new code to APIs that are scheduled for removal. This rule is provided by [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules).

Note: triggering this identifier requires an enum to implement another enum, which PHP itself forbids. PHPStan therefore always also reports a non-ignorable `enumImplements.enum` error alongside this one.

## How to fix it

Remove the deprecated enum from the `implements` clause and use a proper public interface instead:

```diff-php
-enum Color implements OldStatus
+enum Color implements PublicInterface
 {
 	case Red;
 }
```
