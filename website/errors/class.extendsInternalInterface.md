---
title: "class.extendsInternalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-package/src/InternalInterface.php:
// /** @internal */
// interface InternalInterface {}

// In your code:
class MyClass extends \SomePackage\InternalInterface
{
}
```

## Why is it reported?

The class extends a type that is marked as `@internal` and whose type description is an interface. Internal types are not part of the package's public API and may change or be removed without notice in future versions. Depending on internal types in your code makes it fragile and prone to breaking when the dependency is updated.

Note that in valid PHP, a class cannot extend an interface (it should use `implements` instead). This error identifies the usage of an internal interface in the `extends` clause, which is both a misuse of the `extends` keyword and a violation of the internal API boundary.

## How to fix it

If the type is an interface, use `implements` instead of `extends`, and use a non-internal interface:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends \SomePackage\InternalInterface
+class MyClass implements \SomePackage\PublicInterface
 {
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
