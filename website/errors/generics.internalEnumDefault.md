---
title: "generics.internalEnumDefault"
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
 * @template T = InternalStatus
 */
class Container
{
}
```

## Why is it reported?

A `@template` tag uses an internal enum as its default type. The enum referenced in the default value of the template parameter has been marked with the `@internal` PHPDoc tag. Internal enums are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Referencing an internal enum as a template default from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal enum with a public (non-internal) enum in the template default:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = InternalStatus
+ * @template T = PublicStatus
  */
 class Container
 {
 }
```
