---
title: "function.impossibleType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $value): void
{
	if (is_int($value)) {
		// ...
	}
}
```

## Why is it reported?

A type-checking function call will always evaluate to `false` because the checked type is incompatible with the variable's type. In the example above, `$value` is typed as `string`, so `is_int($value)` can never be `true`. This usually indicates a logic error or an incorrect type declaration.

## How to fix it

Fix the type declaration to match the actual range of values, or fix the type check:

```diff-php
-function doFoo(string $value): void
+function doFoo(string|int $value): void
 {
 	if (is_int($value)) {
 		// ...
 	}
 }
```

Or fix the condition to check for the correct type:

```diff-php
 function doFoo(string $value): void
 {
-	if (is_int($value)) {
+	if (is_numeric($value)) {
 		// ...
 	}
 }
```
