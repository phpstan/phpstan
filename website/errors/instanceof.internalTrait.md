---
title: "instanceof.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
trait InternalTrait
{
}

// In your code:

function doFoo(object $obj): void
{
	if ($obj instanceof InternalTrait) {
		// ...
	}
}
```

## Why is it reported?

The `instanceof` check references a trait that is marked as `@internal`. Internal types are implementation details of their package and may change or be removed without notice. Code outside the package should not depend on them, even in type checks.

## How to fix it

Use a public interface or class instead of the internal trait in the `instanceof` check:

```diff-php
 function doFoo(object $obj): void
 {
-	if ($obj instanceof InternalTrait) {
+	if ($obj instanceof PublicInterface) {
 		// ...
 	}
 }
```

If the library does not expose a suitable public type, consider requesting one from the library maintainers.
