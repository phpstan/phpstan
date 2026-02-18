---
title: "generics.internalTraitBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
trait InternalTrait
{
}

// In your code:

/**
 * @template T of InternalTrait
 */
class Wrapper
{
}
```

## Why is it reported?

The `@template` tag bound references a trait marked as `@internal`. Internal types are implementation details of their package and should not be referenced from outside that package. Using an internal trait as a generic bound creates a dependency on an API that may change without notice.

## How to fix it

Replace the internal trait with a public type in the template bound:

```diff-php
 /**
- * @template T of InternalTrait
+ * @template T of PublicInterface
  */
 class Wrapper
 {
 }
```

If no public alternative exists, consider whether the generic bound is necessary, or request that the library expose a public API for the use case.
