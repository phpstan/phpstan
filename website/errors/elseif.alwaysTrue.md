---
title: "elseif.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function classify(int $value): string
{
	if ($value > 0) {
		return 'positive';
	} elseif (is_int($value)) {
		return 'zero or negative';
	}

	return 'unknown';
}
```

## Why is it reported?

The condition in the `elseif` branch always evaluates to `true`. This means the `elseif` is equivalent to a plain `else`, and any code after it is unreachable. This typically indicates that the condition is redundant or that the logic does not match the developer's intent.

In the example above, `$value` is typed as `int`, so `is_int($value)` is always `true` regardless of the `if` branch above.

## How to fix it

Replace the `elseif` with `else` if the condition is truly unnecessary:

```diff-php
 <?php declare(strict_types = 1);

 function classify(int $value): string
 {
 	if ($value > 0) {
 		return 'positive';
-	} elseif (is_int($value)) {
+	} else {
 		return 'zero or negative';
 	}
-
-	return 'unknown';
 }
```

Or write a more specific condition if different cases need to be distinguished:

```diff-php
 <?php declare(strict_types = 1);

 function classify(int $value): string
 {
 	if ($value > 0) {
 		return 'positive';
-	} elseif (is_int($value)) {
-		return 'zero or negative';
+	} elseif ($value === 0) {
+		return 'zero';
+	} else {
+		return 'negative';
 	}
-
-	return 'unknown';
 }
```
