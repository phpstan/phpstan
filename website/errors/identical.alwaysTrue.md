---
title: "identical.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$value = 1;
	if ($value === 1) {
		// always entered
	}
}
```

## Why is it reported?

The strict comparison using `===` always evaluates to `true` because both sides are known to have the same value at that point in the code. In the example above, `$value` is always `1`, so comparing it with `===` to `1` is always `true`. This usually indicates a redundant check or a logic error.

## How to fix it

Remove the redundant comparison:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
 	$value = 1;
-	if ($value === 1) {
-		// always entered
-	}
+	// Execute the code unconditionally
 }
```

Or fix the logic to compare the correct variable:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$value = 1;
-	if ($value === 1) {
+	if ($i === 1) {
 		// now depends on the actual input
 	}
 }
```
