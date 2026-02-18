---
title: "logicalAnd.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$result = $i > 0 and $i > 0;
	if ($result) {
		// ...
	}
}
```

## Why is it reported?

The result of the `and` expression is always `true`. Both sides of the logical AND are always true given the types and conditions involved, making the check redundant. This often indicates duplicated conditions or overly broad type constraints that make the test meaningless.

## How to fix it

Remove the redundant condition:

```diff-php
 function doFoo(int $i): void
 {
-	$result = $i > 0 and $i > 0;
+	$result = $i > 0;
 	if ($result) {
 		// ...
 	}
 }
```

Or replace the duplicated condition with the intended check:

```diff-php
 function doFoo(int $i): void
 {
-	$result = $i > 0 and $i > 0;
+	$result = $i > 0 and $i < 100;
 	if ($result) {
 		// ...
 	}
 }
```
