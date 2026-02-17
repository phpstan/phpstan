---
title: "closure.unusedUse"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $used, string $unused): void
{
	$fn = function () use ($used, $unused) {
		echo $used;
	};
}
```

## Why is it reported?

The anonymous function (closure) imports the variable `$unused` via the `use` clause, but never references it within the closure body. Importing unused variables into a closure is unnecessary and makes the code harder to understand. It may also indicate a mistake where the variable was intended to be used but was forgotten.

## How to fix it

Remove the unused variable from the `use` clause:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(string $used, string $unused): void
 {
-	$fn = function () use ($used, $unused) {
+	$fn = function () use ($used) {
 		echo $used;
 	};
 }
```

Or use the variable inside the closure if it was intended to be used:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(string $used, string $unused): void
 {
 	$fn = function () use ($used, $unused) {
 		echo $used;
+		echo $unused;
 	};
 }
```
