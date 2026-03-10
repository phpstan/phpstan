---
title: "enum.implementsInternalInterface"
shortDescription: "Enum implements an internal interface from another package."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1); // lint >= 8.1

/** @internal */
interface InternalContract
{
}

enum Status implements InternalContract
{
	case Active;
	case Inactive;
}
```

## Why is it reported?

The enum implements an interface that is marked as `@internal`. Internal interfaces are not meant to be used outside of the package that defines them. The library author may change or remove the interface without considering it a breaking change.

## How to fix it

Stop implementing the internal interface and use a public interface provided by the library instead:

```diff-php
-enum Status implements InternalContract
+enum Status implements PublicContract
 {
 	case Active;
 	case Inactive;
 }
```

If no suitable public interface exists, contact the library maintainers to request one.
