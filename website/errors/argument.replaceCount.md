---
title: "argument.replaceCount"
shortDescription: "A replace function is called with an array of replacements shorter than the array of search values."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $str): string
{
	$search = ['a', 'b', 'c'];
	$replace = ['x', 'y'];

	return str_replace($search, $replace, $str);
}
```

## Why is it reported?

When `str_replace()`, `str_ireplace()`, `preg_replace()`, or `preg_filter()` is called with arrays for both the search and the replacement arguments, each search value is replaced by the replacement at the same position. If the replacement array has fewer elements than the search array, PHP silently replaces the remaining search values with an empty string.

In the example above, `'a'` is replaced by `'x'` and `'b'` by `'y'`, but `'c'` is removed from the string instead of being replaced. This is rarely intentional and usually means a replacement value was forgotten.

The error is not reported when the replacement is a string (the same string is used for every search value), or when the replacement is an explicitly empty array, which is a deliberate way to remove all search values.

This rule is currently available in [bleeding edge](/blog/what-is-bleeding-edge) on level 5 and higher.

## How to fix it

Add the missing replacement values so both arrays have the same number of elements:

```diff-php
 function doFoo(string $str): string
 {
 	$search = ['a', 'b', 'c'];
-	$replace = ['x', 'y'];
+	$replace = ['x', 'y', 'z'];

 	return str_replace($search, $replace, $str);
 }
```

If the remaining search values should be removed, make that explicit by passing an empty string for each of them:

```diff-php
 function doFoo(string $str): string
 {
 	$search = ['a', 'b', 'c'];
-	$replace = ['x', 'y'];
+	$replace = ['x', 'y', ''];

 	return str_replace($search, $replace, $str);
 }
```

If all search values should be replaced by the same value, pass a string instead of an array:

```diff-php
 function doFoo(string $str): string
 {
 	$search = ['a', 'b', 'c'];
-	$replace = ['x', 'y'];

-	return str_replace($search, $replace, $str);
+	return str_replace($search, 'x', $str);
 }
```
