---
title: "return.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
	return 'hello';
}
```

## Why is it reported?

The returned value does not match the declared return type. The function declares that it returns `int`, but the actual returned value is `string`. This is a type mismatch that will cause a `TypeError` at runtime in strict mode, or an implicit type coercion in non-strict mode that may lead to data loss or unexpected behavior.

## How to fix it

Return a value that matches the declared return type:

```diff-php
 function doFoo(): int
 {
-	return 'hello';
+	return 42;
 }
```

Or change the return type to match the returned value:

```diff-php
-function doFoo(): int
+function doFoo(): string
 {
 	return 'hello';
 }
```
