---
title: "method.alreadyNarrowedType"
shortDescription: "Type-checking method call is redundant because the type is already narrowed."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class TypeChecker
{
	/** @phpstan-assert-if-true string $value */
	public function isString(mixed $value): bool
	{
		return is_string($value);
	}
}

function doFoo(TypeChecker $checker, string $value): void
{
	if ($checker->isString($value)) {
		echo 'already known to be string';
	}
}
```

## Why is it reported?

A method call that acts as a type-checking or type-narrowing operation always evaluates to `true`. PHPStan has already inferred enough type information to determine that the result of this method call is always `true` in this context.

In the example above, `$value` is already typed as `string`, and `TypeChecker::isString()` uses `@phpstan-assert-if-true string $value` to narrow the type. Since the type is already `string`, the method will always return `true`.

This typically occurs when the same type check is performed redundantly, or when the parameter types already guarantee the result.

## How to fix it

Remove the redundant check if the type is already known:

```diff-php
 function doFoo(TypeChecker $checker, string $value): void
 {
-	if ($checker->isString($value)) {
-		echo 'already known to be string';
-	}
+	echo 'already known to be string';
 }
```

Or fix the parameter type if the check should be meaningful:

```diff-php
-function doFoo(TypeChecker $checker, string $value): void
+function doFoo(TypeChecker $checker, mixed $value): void
 {
 	if ($checker->isString($value)) {
-		echo 'already known to be string';
+		echo 'confirmed string';
 	}
 }
```
