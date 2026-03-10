---
title: "elseif.alwaysTrue"
shortDescription: "Elseif condition is always true, making subsequent branches unreachable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function classify(int $value): string
{
	$flag = 1;
	if ($value > 0) {
		return 'positive';
	} elseif ($flag) {
		return 'zero or negative';
	} else {
		return 'unknown';
	}
}
```

## Why is it reported?

The condition in the `elseif` branch always evaluates to `true`. This means the `elseif` is equivalent to a plain `else`, and any branches after it are unreachable. This typically indicates that the condition is redundant or that the logic does not match the developer's intent.

In the example above, `$flag` is always `1` (truthy), so the `elseif` branch is always entered when `$value > 0` is false, making the `else` branch unreachable.

## How to fix it

Replace the `elseif` with `else` if the condition is truly unnecessary:

```diff-php
 <?php declare(strict_types = 1);

 function classify(int $value): string
 {
-	$flag = 1;
 	if ($value > 0) {
 		return 'positive';
-	} elseif ($flag) {
+	} else {
 		return 'zero or negative';
-	} else {
-		return 'unknown';
 	}
 }
```

Or write a more specific condition if different cases need to be distinguished:

```diff-php
 <?php declare(strict_types = 1);

 function classify(int $value): string
 {
-	$flag = 1;
 	if ($value > 0) {
 		return 'positive';
-	} elseif ($flag) {
-		return 'zero or negative';
+	} elseif ($value === 0) {
+		return 'zero';
 	} else {
-		return 'unknown';
+		return 'negative';
 	}
 }
```
