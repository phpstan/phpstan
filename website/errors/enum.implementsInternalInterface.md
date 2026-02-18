---
title: "enum.implementsInternalInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:
// /** @internal */
// interface InternalInterface {}

enum Status implements \SomeLibrary\InternalInterface
{
	case Active;
	case Inactive;
}
```

## Why is it reported?

The enum implements an interface that is marked as `@internal` by its declaring package. Internal interfaces are not meant to be used outside of the package that defines them. The library author may change or remove the interface without considering it a breaking change.

## How to fix it

Stop implementing the internal interface and use a public interface provided by the library instead:

```diff-php
-enum Status implements \SomeLibrary\InternalInterface
+enum Status implements \SomeLibrary\PublicInterface
 {
 	case Active;
 	case Inactive;
 }
```

If no suitable public interface exists, contact the library maintainers to request one.
