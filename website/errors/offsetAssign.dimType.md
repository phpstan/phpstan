---
title: "offsetAssign.dimType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	$value = 'Hello';
	$value['foo'] = 'x'; // error: Cannot assign offset 'foo' to string.
}
```

## Why is it reported?

Strings in PHP support offset access only with integer keys (e.g., `$str[0] = 'a'`). Attempting to assign a value at a non-integer offset (like a string key) to a string variable is not valid. Similarly, appending with `$str[] = ...` is not supported for strings. This will result in a runtime error.

## How to fix it

If the variable should be an array, initialize it as one.

```diff-php
 function doFoo(): void
 {
-	$value = 'Hello';
+	$value = [];
 	$value['foo'] = 'x';
 }
```

If working with a string, use an integer offset.

```diff-php
 function doFoo(): void
 {
 	$value = 'Hello';
-	$value['foo'] = 'x';
+	$value[0] = 'x';
 }
```
