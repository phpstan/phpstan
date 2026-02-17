---
title: "classConstant.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library (namespace SomeLibrary):

/** @internal */
enum InternalStatus
{
	case Active;
	case Inactive;

	public const DEFAULT = self::Active;
}

// In your code (namespace App):

echo InternalStatus::DEFAULT;
```

## Why is it reported?

The code accesses a class constant on an enum that is marked with the `@internal` PHPDoc tag. Internal enums are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Accessing constants on an internal enum from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Use a constant from a public (non-internal) enum or class instead:

```diff-php
 <?php declare(strict_types = 1);

-echo InternalStatus::DEFAULT;
+echo PublicStatus::DEFAULT;
```

If no public alternative exists, contact the library maintainer to request a public API for the constant, or define the constant directly in the application code.
