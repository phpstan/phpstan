---
title: "phpstan.debugScope"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use function PHPStan\debugScope;

function doFoo(int $a, int $b): void
{
	debugScope(); // Reports current scope: $a: int, $b: int
}
```

## Why is it reported?

This is a debugging tool built into PHPStan. When the `PHPStan\debugScope()` function is called in the analysed code, PHPStan outputs all the variables and their types that are currently in scope at that point. This is useful for debugging PHPStan's type inference -- for understanding what types PHPStan has inferred for each variable at a specific location in the code.

The error message contains a list of all variables in the current scope along with their types, including both the PHPDoc-level type and the native type.

This error is not ignorable because it is a debugging utility intended to be removed after the investigation is complete.

## How to fix it

Remove the `debugScope()` call once debugging is complete:

```diff-php
 <?php declare(strict_types = 1);

-use function PHPStan\debugScope;
-
 function doFoo(int $a, int $b): void
 {
-	debugScope();
+	// actual code here
 }
```
