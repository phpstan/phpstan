---
title: "function.internal"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

namespace App;

function doFoo(): void
{
	\SomeLibrary\internalHelper();
}
```

Where `\SomeLibrary\internalHelper()` is declared as:

```php
namespace SomeLibrary;

/** @internal */
function internalHelper(): void
{
}
```

## Why is it reported?

The function being called is marked with the `@internal` PHPDoc tag. Internal functions are not part of the public API and are intended to be used only within the package or root namespace where they are defined. Calling an internal function from outside its root namespace creates a dependency on an implementation detail that may change or be removed without notice in future versions.

## How to fix it

Use a public API function provided by the library instead:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 function doFoo(): void
 {
-	\SomeLibrary\internalHelper();
+	\SomeLibrary\publicHelper();
 }
```

If no public alternative exists, contact the library maintainer to request a public API for the functionality, or implement the needed functionality directly in the application code.
