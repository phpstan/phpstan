---
title: "class.implementsInternalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-package/src/InternalInterface.php:
// /** @internal */
// interface InternalInterface {}

// In your code:
class MyClass implements \SomePackage\InternalInterface
{
}
```

## Why is it reported?

The class implements an interface that is marked as `@internal`. Internal interfaces are not part of the package's public API and may change or be removed without notice in future versions. Depending on internal interfaces in your code makes it fragile and prone to breaking when the dependency is updated.

## How to fix it

Use only public, non-internal interfaces from the package. Check the package documentation for the intended public API:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass implements \SomePackage\InternalInterface
+class MyClass implements \SomePackage\PublicInterface
 {
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
