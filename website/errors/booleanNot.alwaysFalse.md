---
title: "booleanNot.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$one = 1;
	if (!$one) {
		// never entered
	}
}
```

## Why is it reported?

The negated boolean expression (`!$one`) always evaluates to `false` because the operand is always truthy. In this example, the variable `$one` is always `1`, which is truthy in PHP, so `!$one` is always `false`. This means the condition will never be entered, which usually indicates a logic error or dead code.

## How to fix it

Remove the unreachable condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
 	$one = 1;
-	if (!$one) {
-		// never entered
-	}
 }
```

Or fix the logic to check the correct variable or expression.
