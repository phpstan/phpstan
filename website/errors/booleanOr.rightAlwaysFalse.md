---
title: "booleanOr.rightAlwaysFalse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $i): void
{
	if ($i > 0 || $i > 5) {
		// ...
	}
}
```

## Why is it reported?

The right side of the `||` operator always evaluates to `false`. After evaluating the left side, the type of the right-side expression is narrowed in a way that makes it always `false`. In the example above, if `$i > 0` is `false`, then `$i <= 0`, which means `$i > 5` is always `false`.

## How to fix it

Remove the redundant right-side condition or fix the logic:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(int $i): void
 {
-	if ($i > 0 || $i > 5) {
+	if ($i > 0) {
 		// ...
 	}
 }
```
