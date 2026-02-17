---
title: "if.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function greet(string $name): void
{
	if (is_string($name)) {
		echo 'Hello, ' . $name;
	}
}
```

## Why is it reported?

The `if` condition is always true based on the types and values PHPStan has inferred at that point in the code. This means the `if` branch will always execute, making the condition redundant. This usually points to an unnecessary check, a logic error, or a misunderstanding of the types involved.

## How to fix it

Remove the redundant condition if the check is unnecessary:

```diff-php
 <?php declare(strict_types = 1);

 function greet(string $name): void
 {
-	if (is_string($name)) {
-		echo 'Hello, ' . $name;
-	}
+	echo 'Hello, ' . $name;
 }
```

If the condition was meant to distinguish between different cases, fix the condition to check what was actually intended:

```diff-php
 <?php declare(strict_types = 1);

 function greet(string $name): void
 {
-	if (is_string($name)) {
+	if ($name !== '') {
 		echo 'Hello, ' . $name;
 	}
 }
```
