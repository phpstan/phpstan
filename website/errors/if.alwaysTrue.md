---
title: "if.alwaysTrue"
shortDescription: "Condition in an if statement is always true, making the check redundant."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$x = 5;
	if ($x) {
		echo 'always reached';
	}
}
```

## Why is it reported?

The `if` condition is always true based on the types and values PHPStan has inferred at that point in the code. This means the `if` branch will always execute, making the condition redundant. This usually points to an unnecessary check, a logic error, or a misunderstanding of the types involved.

In the example above, `$x` is always `5` (truthy), so the condition is always satisfied.

## How to fix it

Remove the redundant condition if the check is unnecessary:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	$x = 5;
-	if ($x) {
-		echo 'always reached';
-	}
+	echo 'always reached';
 }
```

If the condition was meant to distinguish between different cases, fix the condition to check what was actually intended:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(int $x): void
 {
-	$x = 5;
-	if ($x) {
-		echo 'always reached';
+	if ($x > 0) {
+		echo 'positive';
 	}
 }
```
