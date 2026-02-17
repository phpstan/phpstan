---
title: "phpstan.superType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use function PHPStan\Testing\assertSuperType;

function doFoo(int $a): void
{
	assertSuperType('string', $a); // ERROR: Expected subtype of string, actual: int
}
```

## Why is it reported?

The `PHPStan\Testing\assertSuperType()` function is a testing and debugging tool built into PHPStan. It asserts that the type of the second argument is a subtype of the type specified in the first argument (a type string). When the assertion fails -- meaning the actual type is not a subtype of the expected super type -- this error is reported.

This function is primarily used in PHPStan's own test suite and in extension development to verify that PHPStan correctly infers types. It ensures that the inferred type is at least as specific as (or more specific than) the expected type.

This error is not ignorable because it represents a failed type assertion that should be addressed by fixing either the code or the expectation.

## How to fix it

Either fix the code so that the type matches the assertion:

```diff-php
 <?php declare(strict_types = 1);

 use function PHPStan\Testing\assertSuperType;

-function doFoo(int $a): void
+function doFoo(string $a): void
 {
 	assertSuperType('string', $a);
 }
```

Or update the assertion to match the actual type:

```diff-php
 <?php declare(strict_types = 1);

 use function PHPStan\Testing\assertSuperType;

 function doFoo(int $a): void
 {
-	assertSuperType('string', $a);
+	assertSuperType('int', $a);
 }
```

Or remove the assertion once debugging is complete:

```diff-php
 <?php declare(strict_types = 1);

-use function PHPStan\Testing\assertSuperType;
-
 function doFoo(int $a): void
 {
-	assertSuperType('string', $a);
+	// actual code here
 }
```
