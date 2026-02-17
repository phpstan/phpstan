---
title: "catch.internalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
class InternalException extends \RuntimeException
{
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\InternalException;

try {
	// ...
} catch (InternalException $e) {
	// ...
}
```

## Why is it reported?

A `catch` block is catching an exception class that is marked as `@internal`. Internal classes are not meant to be used outside of the package or namespace where they are defined. Catching internal exceptions creates a dependency on implementation details that may change without notice in future versions.

## How to fix it

Catch a public (non-internal) parent exception class instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\InternalException;

 try {
 	// ...
-} catch (InternalException $e) {
+} catch (\RuntimeException $e) {
 	// ...
 }
```

If the library provides a public exception class or interface for this purpose, catch that instead.
