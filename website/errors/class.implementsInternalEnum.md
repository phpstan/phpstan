---
title: "class.implementsInternalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum Status
{
	case Active;
	case Inactive;
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\Status;

class Foo implements Status
{
}
```

## Why is it reported?

A class implements an enum that is marked as `@internal`. Internal types are not meant to be used outside the package or namespace where they are defined. While enums cannot actually be implemented by classes in PHP, PHPStan reports this as an internal usage violation because the reference to the internal type is itself problematic.

## How to fix it

Use a public (non-internal) interface or class instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\Status;
+use SomeLibrary\StatusInterface;

-class Foo implements Status
+class Foo implements StatusInterface
 {
 }
```

If the library provides a public interface for this purpose, use that instead.
