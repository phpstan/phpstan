---
title: "echo.nonString"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	echo [];
}
```

## Why is it reported?

The `echo` language construct expects values that can be converted to a string. Passing a value that cannot be converted to a string -- such as an array or an object without a `__toString()` method -- will cause a `TypeError` at runtime. In the example above, an array is passed to `echo`, which PHP cannot convert to a string.

## How to fix it

Convert the value to a string before passing it to `echo`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	echo [];
+	echo implode(', ', []);
 }
```

Or use a function designed for the value type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	echo [];
+	print_r([]);
 }
```
