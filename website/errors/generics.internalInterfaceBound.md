---
title: "generics.internalInterfaceBound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:
// /** @internal */
// interface InternalInterface {}

/**
 * @template T of \SomeLibrary\InternalInterface
 */
class Foo
{
}
```

## Why is it reported?

A template type bound references an interface that is marked as `@internal` by its declaring package. The `@template T of SomeInterface` syntax constrains the template type to subtypes of the given interface. When that interface is internal, using it as a bound creates a dependency on an implementation detail that may change without notice.

## How to fix it

Use a public interface as the template type bound instead:

```diff-php
 /**
- * @template T of \SomeLibrary\InternalInterface
+ * @template T of \SomeLibrary\PublicInterface
  */
 class Foo
 {
 }
```

If no suitable public interface exists, consider removing the bound or contacting the library maintainers:

```diff-php
 /**
- * @template T of \SomeLibrary\InternalInterface
+ * @template T
  */
 class Foo
 {
 }
```
