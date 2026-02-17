---
title: "classConstant.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead. */
interface OldInterface
{
    public const FOO = 'foo';
}

// Accessing a constant on a deprecated interface
echo OldInterface::FOO;
```

## Why is it reported?

The code accesses a class constant on an interface that has been marked with a `@deprecated` PHPDoc tag. The interface is scheduled for removal or replacement, and any usage of its constants should be migrated to the recommended alternative.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the usage with the constant from the non-deprecated replacement, as indicated in the deprecation message:

```diff-php
 <?php declare(strict_types = 1);

-echo OldInterface::FOO;
+echo NewInterface::FOO;
```
