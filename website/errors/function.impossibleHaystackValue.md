---
title: "function.impossibleHaystackValue"
shortDescription: "A value in a constant array haystack passed to in_array(), array_search() or array_keys() can never match the needle type."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Foo
{
	case ONE;
	case TWO;
}

function doFoo(int $i): void
{
	// Foo::ONE can never be an int, but 1 and 2 can.
	if (in_array($i, [Foo::ONE, 1, 2], true)) {
		echo 'yes';
	}
}
```

## Why is it reported?

The haystack passed to `in_array()`, `array_search()` or `array_keys()` is a constant array, so PHPStan knows every value it contains. When a value in that array can never be equal (`==`) or identical (`===`, when the strict flag is `true`) to the needle type, comparing against it is pointless — that element can never produce a match.

In the example above, the needle `$i` is an `int`, so it can never be identical to the enum case `Foo::ONE`. The `Foo::ONE` element in the haystack is dead: only `1` and `2` can ever match. This usually points to a wrong value in the array or an incorrect needle type.

Only values with a finite type (such as enum cases, `true`, `false`, `null`, or constant scalars) are reported, since only those are certain never to match. If *no* haystack value can ever match, the whole `in_array()` call is impossible and is reported by [`function.impossibleType`](/error-identifiers/function.impossibleType) instead.

This rule is only applied at [rule level](/user-guide/rule-levels) 4 and above, and is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Remove the value that can never match:

```diff-php
-	if (in_array($i, [Foo::ONE, 1, 2], true)) {
+	if (in_array($i, [1, 2], true)) {
 		echo 'yes';
 	}
```

Or, if the extra value was intended to be matched, fix the needle type so it can actually contain that value:

```diff-php
-function doFoo(int $i): void
+function doFoo(int|Foo $i): void
 {
 	if (in_array($i, [Foo::ONE, 1, 2], true)) {
 		echo 'yes';
 	}
 }
```

On PHP versions without native union types (before PHP 8.0), express the union in PHPDoc instead. See [PHPDoc Types](/writing-php-code/phpdoc-types):

```diff-php
+/**
+ * @param int|Foo $i
+ */
 function doFoo($i): void
 {
 	if (in_array($i, [Foo::ONE, 1, 2], true)) {
 		echo 'yes';
 	}
 }
```

By default the needle and haystack types include those from PHPDocs. If you don't want PHPStan to consider PHPDoc types as certain, set [`treatPhpDocTypesAsCertain`](/config-reference#treatphpdoctypesascertain) to `false`.
