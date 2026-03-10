---
title: "interface.disallowedSubtype"
shortDescription: "Interface extends a sealed interface that does not allow this subtype."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-sealed AllowedInterface */
interface SealedInterface
{
}

interface AllowedInterface extends SealedInterface
{
}

interface DisallowedInterface extends SealedInterface
{
}
```

## Why is it reported?

The interface extends another interface that has been marked as sealed using the `@phpstan-sealed` PHPDoc tag. A sealed interface restricts which types are allowed to extend it. The `DisallowedInterface` is not listed among the allowed subtypes, so PHPStan reports it.

This mechanism enforces closed hierarchies where only specific implementations are permitted, similar to sealed classes in other languages.

## How to fix it

Add the interface to the list of allowed subtypes in the sealed declaration:

```diff-php
-/** @phpstan-sealed AllowedInterface */
+/** @phpstan-sealed AllowedInterface|DisallowedInterface */
 interface SealedInterface
 {
 }
```

Or remove the sealed parent from the interface declaration if it should not extend the sealed interface:

```diff-php
-interface DisallowedInterface extends SealedInterface
+interface DisallowedInterface
 {
 }
```
