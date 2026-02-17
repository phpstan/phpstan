---
title: "catch.internalEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

namespace SomeLibrary;

/** @internal */
enum ErrorCode: int
{
	case NotFound = 404;
	case ServerError = 500;
}
```

```php
<?php declare(strict_types = 1);

// In your code:

namespace App;

use SomeLibrary\ErrorCode;

try {
	// ...
} catch (ErrorCode $e) {
	// ...
}
```

## Why is it reported?

A `catch` block references an enum that is marked as `@internal`. Internal types are not meant to be used outside of the package or namespace where they are defined. Catching internal enums creates a dependency on implementation details that may change without notice in future versions.

## How to fix it

Catch a public (non-internal) exception class instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

-use SomeLibrary\ErrorCode;

 try {
 	// ...
-} catch (ErrorCode $e) {
+} catch (\RuntimeException $e) {
 	// ...
 }
```

If the library provides a public exception class or interface for this purpose, catch that instead.
