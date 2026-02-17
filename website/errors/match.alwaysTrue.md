---
title: "match.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): string
{
	$value = 1;
	return match (true) {
		$value === 1 => 'one',
		$value === 2 => 'two',
		default => 'other',
	};
}
```

## Why is it reported?

A match arm comparison always evaluates to `true`, which means all subsequent arms are unreachable. In the example above, since `$value` is always `1`, the first arm (`$value === 1`) always matches, making the remaining arms dead code.

## How to fix it

Remove the unreachable arms:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): string
 {
 	$value = 1;
-	return match (true) {
-		$value === 1 => 'one',
-		$value === 2 => 'two',
-		default => 'other',
-	};
+	return 'one';
 }
```

Or fix the logic so the match subject varies.
