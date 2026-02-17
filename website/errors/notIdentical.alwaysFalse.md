---
title: "notIdentical.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$value = 1;
	if ($value !== 1) {
		// never entered
	}
}
```

## Why is it reported?

The strict comparison using `!==` always evaluates to `false` because both sides are known to have the same value at that point in the code. In the example above, `$value` is always `1`, so `$value !== 1` is always `false`. This makes the condition unreachable.

## How to fix it

Remove the unreachable condition:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
 	$value = 1;
-	if ($value !== 1) {
-		// never entered
-	}
 }
```

Or fix the logic to compare the correct variable.
