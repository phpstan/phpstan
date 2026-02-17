---
title: "generics.internalInterfaceDefault"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
interface InternalProcessor
{
}

// In your code:

/**
 * @template T = InternalProcessor
 */
class Pipeline
{
}
```

## Why is it reported?

A `@template` tag uses an internal interface as its default type. The interface referenced in the default value of the template parameter has been marked with the `@internal` PHPDoc tag. Internal interfaces are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Referencing an internal interface as a template default from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice.

## How to fix it

Replace the internal interface with a public (non-internal) interface in the template default:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @template T = InternalProcessor
+ * @template T = PublicProcessor
  */
 class Pipeline
 {
 }
```
