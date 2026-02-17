---
title: "doWhile.alwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	do {
		echo 'hello';
	} while (false);
}
```

## Why is it reported?

The condition in the `do-while` loop always evaluates to `false`, meaning the loop body will always execute exactly once. While `do { ... } while (false);` is a valid pattern in some contexts, it may indicate a logic error when the condition was intended to be dynamic.

## How to fix it

If the loop body should only execute once, remove the loop:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	do {
-		echo 'hello';
-	} while (false);
+	echo 'hello';
 }
```

Or fix the condition so it depends on a meaningful expression.
