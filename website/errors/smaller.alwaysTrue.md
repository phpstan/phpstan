---
title: "smaller.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function checkAge(int $age): void
{
	if ($age < PHP_INT_MAX) {
		echo 'Always reaches here';
	}
}
```

## Why is it reported?

The `<` comparison is always true based on the types of the operands. This indicates that the condition is redundant because the left side is always strictly less than the right side given their possible values. Such comparisons often signal a logic error or an overly broad type.

## How to fix it

Remove the unnecessary condition, or adjust the comparison to reflect the actual constraint you intend to enforce.

```diff-php
 <?php declare(strict_types = 1);

-function checkAge(int $age): void
+/**
+ * @param int<0, 150> $age
+ */
+function checkAge(int $age): void
 {
-	if ($age < PHP_INT_MAX) {
+	if ($age < 18) {
 		echo 'Too young';
 	}
 }
```
