---
title: "phpstan.dumpType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, string $b): void
{
	\PHPStan\dumpType($a); // Dumped type: int
	\PHPStan\dumpType($b); // Dumped type: string
}
```

## Why is it reported?

The `PHPStan\dumpType()` function is a debugging tool built into PHPStan. When called, PHPStan reports the type of each argument as an error. This is useful for inspecting what type PHPStan has inferred for a given expression at a specific point in the code.

Each argument passed to `dumpType()` produces a separate error message showing the type PHPStan has determined for that expression, including any type narrowing from conditions, type guards, or other control flow.

This error is not ignorable because it is a debugging utility intended to be removed after the investigation is complete.

## How to fix it

Remove the `\PHPStan\dumpType()` call once the type has been inspected:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $a, string $b): void
 {
-	\PHPStan\dumpType($a);
-	\PHPStan\dumpType($b);
+	// actual code here
 }
```
