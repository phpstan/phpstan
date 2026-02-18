---
title: "enum.implementsDeprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @deprecated */
interface OldContract
{
	public function execute(): void;
}

enum Status implements OldContract
{
	case Active;
	case Inactive;

	public function execute(): void
	{
	}
}
```

## Why is it reported?

The enum implements an interface that is marked as `@deprecated`. Using deprecated interfaces couples new code to APIs that are scheduled for removal, making future upgrades harder. This rule is provided by [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules).

## How to fix it

Switch to the non-deprecated replacement interface, if one exists:

```diff-php
-enum Status implements OldContract
+enum Status implements NewContract
 {
 	case Active;
 	case Inactive;
 }
```

If no replacement exists yet, check the deprecation message for migration guidance.
