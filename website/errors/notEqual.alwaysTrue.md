---
title: "notEqual.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function check(int $a, string $b): void
{
	if ($a != $b) { // ERROR: Loose comparison using != between int and string will always evaluate to true.
		// always reached
	}
}
```

## Why is it reported?

The loose comparison using `!=` between two expressions will always evaluate to true based on the types PHPStan has inferred. This means the condition is always satisfied, making it redundant, and any `else` branch would be dead code.

The `!=` operator performs a loose (type-juggling) comparison. PHPStan reports this when it can determine from the types that the two values can never be loosely equal, regardless of the actual runtime values.

## How to fix it

Review the logic of the comparison. The condition is always true and the else branch (if any) is unreachable.

If the comparison is intentional but the types are wrong, fix the types so the comparison becomes meaningful:

```diff-php
 <?php declare(strict_types = 1);

-function check(int $a, string $b): void
+function check(int $a, int $b): void
 {
 	if ($a != $b) {
 		// ...
 	}
 }
```

Consider using strict comparison (`!==`) instead of loose comparison, which avoids type-juggling surprises:

```diff-php
 <?php declare(strict_types = 1);

 function check(int $a, string $b): void
 {
-	if ($a != $b) {
+	if ($a !== (int) $b) {
 		// ...
 	}
 }
```

If the branch is always taken and the condition is unnecessary, simplify the code by removing the condition:

```diff-php
 <?php declare(strict_types = 1);

 function check(int $a, string $b): void
 {
-	if ($a != $b) {
-		doSomething();
-	}
+	doSomething();
 }
```
