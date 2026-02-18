---
title: "enum.implementsDeprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @deprecated */
interface DeprecatedEnumInterface
{
}

enum Color implements DeprecatedEnumInterface
{
	case Red;
	case Blue;
}
```

## Why is it reported?

The enum implements an interface that is marked as `@deprecated`. Depending on deprecated interfaces ties the enum to an API that is planned for removal. This rule is provided by [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules).

## How to fix it

Replace the deprecated interface with its non-deprecated successor:

```diff-php
-enum Color implements DeprecatedEnumInterface
+enum Color implements ReplacementInterface
 {
 	case Red;
 	case Blue;
 }
```

If no replacement is available, consult the deprecation notice for migration instructions.
