---
title: "nullCoalesce.variable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$value = $undefined ?? 'default';
}
```

## Why is it reported?

The variable on the left side of the `??` (null coalesce) operator is never defined in the current scope. While PHP does not throw an error when using `??` with undefined variables (unlike a direct variable access), this typically indicates a bug -- the variable name may be misspelled, or the code that was supposed to define it is missing.

## How to fix it

Define the variable before using it in the null coalesce expression:

```diff-php
 function doFoo(): void
 {
+	$defined = getData();
-	$value = $undefined ?? 'default';
+	$value = $defined ?? 'default';
 }
```

If the variable is intentionally optional (e.g., comes from an `extract()` call or a conditionally-executed block), restructure the code so the variable is always defined:

```diff-php
 function doFoo(bool $condition): void
 {
+	$result = null;
 	if ($condition) {
 		$result = compute();
 	}
 	$value = $result ?? 'default';
 }
```
