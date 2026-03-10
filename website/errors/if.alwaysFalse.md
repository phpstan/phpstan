---
title: "if.alwaysFalse"
shortDescription: "Condition in an if statement is always false, making the body dead code."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$x = 0;
	if ($x) {
		echo 'never reached';
	}
}
```

## Why is it reported?

The `if` condition is always false based on the types and values PHPStan has inferred at that point in the code. The body of the `if` statement will never execute, making it dead code. This usually points to a logic error or redundant check.

In the example above, `$x` is always `0` (falsy), so the `if` body is unreachable.

## How to fix it

Review the surrounding logic and either remove the dead branch or fix the condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	$x = 0;
-	if ($x) {
-		echo 'never reached';
-	}
+	echo 'always reached';
 }
```

Or fix the variable assignment so the condition can be true:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(int $x): void
 {
-	$x = 0;
 	if ($x) {
-		echo 'never reached';
+		echo 'nonzero';
 	}
 }
```
