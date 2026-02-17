---
title: "instanceof.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(\stdClass $obj): void
{
	if ($obj instanceof \stdClass) {
		// always entered
	}
}
```

## Why is it reported?

The `instanceof` check always evaluates to `true` because the expression is already known to be of the checked type. In the example above, `$obj` is typed as `\stdClass`, so `$obj instanceof \stdClass` is always `true`. This makes the check redundant.

## How to fix it

Remove the redundant `instanceof` check:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(\stdClass $obj): void
 {
-	if ($obj instanceof \stdClass) {
-		// always entered
-	}
+	// Execute the code unconditionally
 }
```
