---
title: "interface.extendsInternalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

// In package vendor/some-package:
// /** @internal */
// interface InternalServiceInterface {}

interface MyServiceInterface extends \Vendor\InternalServiceInterface
{
}
```

## Why is it reported?

The interface extends another interface that is marked as `@internal`. Internal interfaces are not part of the package's public API and may change or be removed without notice in future versions. Extending an internal interface couples your code to implementation details that may break when the dependency is updated.

## How to fix it

Extend a non-internal interface instead, or define the required methods directly:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-interface MyServiceInterface extends \Vendor\InternalServiceInterface
+interface MyServiceInterface
 {
+	public function execute(): void;
 }
```

If the package provides a public interface for the same purpose, extend that instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-interface MyServiceInterface extends \Vendor\InternalServiceInterface
+interface MyServiceInterface extends \Vendor\PublicServiceInterface
 {
 }
```

If no public alternative exists, consider reaching out to the package maintainers to request a public API for your use case.
