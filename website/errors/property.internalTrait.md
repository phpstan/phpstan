---
title: "property.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

use Library\InternalTrait;

class Foo
{
	/** The trait is marked @internal by the library */
	public InternalTrait $helper;
}
```

Where `Library\InternalTrait` is referenced via a native property type intersection or similar type usage, and is:

```php
<?php declare(strict_types = 1);

namespace Library;

/** @internal */
trait InternalTrait
{
	public function doSomething(): void {}
}
```

## Why is it reported?

A property type references a trait that has been marked as `@internal`. Internal traits are implementation details of their package and are not meant to be used by external code. They may change or be removed without notice in future versions. This can be reported for:

- Native property type declarations using internal traits
- Accessing a property on an object whose declaring class is an internal trait

## How to fix it

Replace the internal trait reference with the package's public API:

```diff-php
 class Foo
 {
-	public InternalTrait $helper;
+	public PublicInterface $helper;
 }
```

If no public alternative exists, contact the library maintainers to request a public API for the functionality.
