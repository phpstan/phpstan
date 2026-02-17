---
title: "empty.variable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	empty($undefined);
}
```

## Why is it reported?

The variable used inside `empty()` is never defined in the current scope. The `empty()` language construct is intended to check whether a variable exists and has a non-empty value. When used on a variable that is never defined, the result is always `true` (because undefined variables are treated as empty). This usually indicates a logic error, such as a typo in the variable name or a missing assignment.

## How to fix it

Define the variable before using it with `empty()`, or fix the variable name if it is a typo:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	empty($undefined);
+	$value = getValue();
+	if (empty($value)) {
+		// handle empty case
+	}
 }
```

If the intent is to check whether an optional variable has been set, use `isset()` instead with proper variable initialization:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(?string $value = null): void
 {
-	empty($undefined);
+	if ($value === null || $value === '') {
+		// handle missing/empty case
+	}
 }
```
