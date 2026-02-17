---
title: "phpstan.dumpPhpDocType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @template T
 * @param T $value
 * @return T
 */
function identity($value)
{
	\PHPStan\dumpPhpDocType($value);

	return $value;
}
```

## Why is it reported?

The `PHPStan\dumpPhpDocType()` function is a debugging tool built into PHPStan that outputs the PHPDoc representation of the type of each argument it receives. When called, PHPStan reports the dumped type as an error so the developer can inspect how PHPStan represents the type in PHPDoc notation.

In the example above, calling `\PHPStan\dumpPhpDocType($value)` inside a generic function would produce: `Dumped type: T`.

## How to fix it

Remove the `\PHPStan\dumpPhpDocType()` call once the type has been inspected. It is intended only as a temporary debugging aid during development and should not remain in production code:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @template T
  * @param T $value
  * @return T
  */
 function identity($value)
 {
-	\PHPStan\dumpPhpDocType($value);
-
 	return $value;
 }
```
