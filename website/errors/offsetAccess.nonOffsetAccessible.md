---
title: "offsetAccess.nonOffsetAccessible"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value): void
{
	echo $value[42];
}
```

## Why is it reported?

The code attempts to use array access syntax (`$var[...]` or `$var[]`) on a type that does not support offset access. Types like `int`, `float`, `bool`, `resource`, `stdClass`, and `Closure` do not support the `[]` operator.

Only arrays, strings, and objects implementing `ArrayAccess` support offset access in PHP.

## How to fix it

Fix the type of the variable so it supports offset access:

```diff-php
-function doFoo(int $value): void
+function doFoo(array $value): void
 {
 	echo $value[42];
 }
```

Or fix the access to use a method appropriate for the type:

```diff-php
 function doFoo(stdClass $value): void
 {
-	echo $value['foo'];
+	echo $value->foo;
 }
```

If the variable might or might not be an array, narrow the type before accessing it:

```php
<?php declare(strict_types = 1);

function doFoo(array|int $value): void
{
	if (is_array($value)) {
		echo $value[42];
	}
}
```
