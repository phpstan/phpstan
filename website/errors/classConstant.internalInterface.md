---
title: "classConstant.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
interface InternalConfig
{
    public const MAX_RETRIES = 3;
}

// In your code:

echo InternalConfig::MAX_RETRIES;
```

## Why is it reported?

The code accesses a class constant on an interface that is marked with the `@internal` PHPDoc tag. Internal interfaces are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Accessing constants on an internal interface from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Use a constant from a public (non-internal) interface or class instead:

```diff-php
 <?php declare(strict_types = 1);

-echo InternalConfig::MAX_RETRIES;
+echo PublicConfig::MAX_RETRIES;
```

If no public alternative exists, contact the library maintainer to request a public API for the constant, or define the constant directly in the application code.
