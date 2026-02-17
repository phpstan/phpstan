---
title: "unset.variable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	unset($undefined);
}
```

## Why is it reported?

The `unset()` call targets a variable that does not exist in the current scope. Unsetting an undefined variable is a no-op but usually indicates a bug -- the variable name is likely misspelled, or the logic that was supposed to define the variable is missing or unreachable.

## How to fix it

Make sure the variable name is correct and the variable is defined before attempting to unset it:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	unset($undefined);
+	$data = getData();
+	// ... use $data ...
+	unset($data);
 }
```

If the `unset()` call is no longer needed, remove it:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	unset($undefined);
 }
```
