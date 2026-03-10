---
title: "match.alwaysTrue"
shortDescription: "Match arm condition always matches, making subsequent arms unreachable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): string
{
	$flag = true;
	return match (true) {
		$flag => 'always',
		$i > 0 => 'positive',
		default => 'other',
	};
}
```

## Why is it reported?

A match arm comparison always evaluates to `true`, which means all subsequent arms are unreachable. In the example above, `$flag` is always `true`, so the first arm always matches when compared to the match subject `true`, making the remaining arms dead code.

## How to fix it

Remove the unreachable arms:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): string
 {
-	$flag = true;
-	return match (true) {
-		$flag => 'always',
-		$i > 0 => 'positive',
-		default => 'other',
-	};
+	return 'always';
 }
```

Or fix the logic so the match arm condition can vary:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): string
+function doFoo(int $i, bool $flag): string
 {
-	$flag = true;
 	return match (true) {
 		$flag => 'flagged',
 		$i > 0 => 'positive',
 		default => 'other',
 	};
 }
```
