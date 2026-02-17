---
title: "generics.internalEnumBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
enum InternalStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

// In your code:

/**
 * @template T of InternalStatus
 */
class StatusContainer
{
}
```

## Why is it reported?

A `@template` tag uses an internal enum as its bound type. The enum referenced in the `of` clause has been marked with the `@internal` PHPDoc tag. Internal enums are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Referencing an internal enum as a template bound from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal enum with a public (non-internal) enum or class in the template bound:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of InternalStatus
+ * @template T of PublicStatus
  */
 class StatusContainer
 {
 }
```
