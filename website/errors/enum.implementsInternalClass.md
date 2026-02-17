---
title: "enum.implementsInternalClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In package vendor/some-library:

/** @internal */
class InternalService
{
}

// In your code:

enum Status implements InternalService // error
{
	case Active;
}
```

## Why is it reported?

The enum implements a class that is marked as `@internal`. Internal classes are not meant to be used outside of the package that defines them, as they may change or be removed without notice. Additionally, a class cannot be used in an `implements` clause at all -- only interfaces can.

## How to fix it

Use only public (non-internal) interfaces in the `implements` clause of your enum:

```diff-php
 <?php declare(strict_types = 1);

-enum Status implements InternalService
+enum Status implements PublicInterface
 {
 	case Active;
 }
```
