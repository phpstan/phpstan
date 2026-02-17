---
title: "generics.internalClassBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
class InternalHandler
{
}

// In your code:

/**
 * @template T of InternalHandler
 */
class Pipeline
{
}
```

## Why is it reported?

A `@template` tag uses an internal class as its bound type. The class referenced in the `of` clause has been marked with the `@internal` PHPDoc tag. Internal classes are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Referencing an internal class as a template bound from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal class with a public (non-internal) class in the template bound:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T of InternalHandler
+ * @template T of PublicHandler
  */
 class Pipeline
 {
 }
```
