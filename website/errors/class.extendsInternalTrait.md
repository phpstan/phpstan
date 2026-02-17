---
title: "class.extendsInternalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-package/src/InternalTrait.php:
// /** @internal */
// trait InternalTrait {}

// In your code:
class MyClass extends \SomePackage\InternalTrait
{
}
```

## Why is it reported?

The class extends a type that is marked as `@internal` and is a trait. Internal types are not part of the package's public API and may change or be removed without notice in future versions. Additionally, traits cannot be extended in PHP -- they should be used with the `use` keyword instead.

## How to fix it

Do not extend internal traits. If a trait is needed, use the `use` keyword with a non-internal trait, and rely on the package's public API:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends \SomePackage\InternalTrait
+class MyClass
 {
+    // Use the package's public API instead
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
