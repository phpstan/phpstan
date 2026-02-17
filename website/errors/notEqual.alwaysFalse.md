---
title: "notEqual.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function check(int $value): void
{
	if ($value != $value) {
		// unreachable
	}
}
```

## Why is it reported?

The loose comparison using `!=` between two expressions will always evaluate to false based on the types PHPStan has inferred. This means the condition can never be true, so any code inside the branch is dead code.

The `!=` operator performs a loose (type-juggling) comparison. PHPStan reports this when it can determine from the types that the comparison will always yield false, regardless of the actual values at runtime.

## How to fix it

Review the logic of the comparison. The condition is redundant and the code inside the branch is unreachable.

If the comparison is intentional but the types are wrong, fix the types so the comparison becomes meaningful:

```diff-php
 <?php declare(strict_types = 1);

-function check(int $value): void
+function check(int $value, int $other): void
 {
-	if ($value != $value) {
+	if ($value != $other) {
 		// ...
 	}
 }
```

If the branch is no longer needed, remove it:

```diff-php
 <?php declare(strict_types = 1);

 function check(int $value): void
 {
-	if ($value != $value) {
-		// unreachable
-	}
 }
```
