---
title: "generics.internalTraitDefault"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
trait InternalTrait
{
    public function doSomething(): void
    {
    }
}

// In your code:

/**
 * @template T = InternalTrait
 */
class Container
{
}
```

## Why is it reported?

A `@template` tag uses an internal trait as its default type. The trait referenced in the default value of the template parameter has been marked with the `@internal` PHPDoc tag. Internal traits are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Referencing an internal trait as a template default from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal trait with a public (non-internal) type in the template default:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = InternalTrait
+ * @template T = PublicInterface
  */
 class Container
 {
 }
```
