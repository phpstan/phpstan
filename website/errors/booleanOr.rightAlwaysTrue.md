---
title: "booleanOr.rightAlwaysTrue"
shortDescription: "Right side of || always evaluates to true, making the whole expression always true."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	$t = true;
	if ($i > 0 || $t) {
		echo 'right always true';
	}
}
```

## Why is it reported?

The right side of a `||` expression always evaluates to `true`. This means the entire expression is always `true` regardless of what the left side evaluates to, which usually indicates a logic error or a redundant condition.

In the example above, `$t` is always `true`, so the right side of `||` is always `true` when it is evaluated (i.e., when `$i > 0` is `false`). This makes the whole condition always `true`.

## How to fix it

Simplify the condition if it is redundant:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	$t = true;
-	if ($i > 0 || $t) {
-		echo 'right always true';
-	}
+	echo 'right always true';
 }
```

Or fix the right side so it can evaluate to either `true` or `false`:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $i): void
+function doFoo(int $i, bool $flag): void
 {
-	$t = true;
-	if ($i > 0 || $t) {
+	if ($i > 0 || $flag) {
 		// ...
 	}
 }
```
