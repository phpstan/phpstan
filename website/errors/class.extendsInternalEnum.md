---
title: "class.extendsInternalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-package/src/Status.php:
// /** @internal */
// enum Status { case Active; }

// In your code:
class MyClass extends \SomePackage\Status
{
}
```

## Why is it reported?

The class extends a type that is marked as `@internal` and is an enum. Internal types are not part of the package's public API and may change or be removed without notice in future versions. Additionally, enums cannot be extended in PHP at all, making this doubly invalid.

## How to fix it

Do not extend internal enums. Use the public API of the package instead, or use composition:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends \SomePackage\Status
+class MyClass
 {
+    // Use the package's public API instead
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
