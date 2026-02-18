---
title: "class.implementsInternalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:
namespace SomeLibrary;

/** @internal */
trait InternalHelper
{
	public function helper(): void {}
}

// In your code:
namespace App;

class MyClass
{
	use \SomeLibrary\InternalHelper;
}
```

## Why is it reported?

The trait being used is marked as `@internal`, meaning it is not part of the library's public API and may change or be removed without notice in future versions. Using internal traits from third-party packages makes the code dependent on implementation details that are not guaranteed to remain stable.

## How to fix it

Replace the internal trait with a public API alternative, or implement the needed functionality directly:

```diff-php
 <?php declare(strict_types = 1);

 namespace App;

 class MyClass
 {
-	use \SomeLibrary\InternalHelper;
+
+	public function helper(): void
+	{
+		// Own implementation
+	}
 }
```
