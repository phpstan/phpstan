---
title: "property.internalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Library\InternalInterface;

class Foo
{
	/** The interface is marked @internal by the library */
	public InternalInterface $service;
}
```

Where `Library\InternalInterface` is:

```php
<?php declare(strict_types = 1);

namespace Library;

/** @internal */
interface InternalInterface
{
	public function execute(): void;
}
```

## Why is it reported?

A property type references an interface that has been marked as `@internal`. Internal interfaces are implementation details of their package and are not meant to be used by external code. They may change or be removed without notice in future versions. This can be reported for:

- Native property type declarations using internal interfaces
- Accessing a property on an object whose declaring class is an internal interface

## How to fix it

Replace the internal interface with the package's public API:

```diff-php
 class Foo
 {
-	public InternalInterface $service;
+	public PublicInterface $service;
 }
```

If no public alternative exists, contact the library maintainers to request a public API for the functionality.
