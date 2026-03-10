---
title: "method.impossibleType"
shortDescription: "Type-checking method call always evaluates to false."
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

function doFoo(TypeChecker $checker, int $value): void
{
	if ($checker->isString($value)) {
		echo 'impossible';
	}
}
```

## Why is it reported?

A method call that acts as a type check always evaluates to `false` based on the types PHPStan knows at that point. This means the condition can never be satisfied, so the code inside the branch is dead code.

In the example above, `$value` is typed as `int`, and `TypeChecker::isString()` uses `@phpstan-assert-if-true string $value` to assert the value is a `string`. Since `int` and `string` are incompatible types, the method will always return `false`.

## How to fix it

Remove the dead branch if the type check is impossible:

```diff-php
 function doFoo(TypeChecker $checker, int $value): void
 {
-	if ($checker->isString($value)) {
-		echo 'impossible';
-	}
+	// $value is always int, no need to check for string
 }
```

Or fix the parameter type if the check should be meaningful:

```diff-php
-function doFoo(TypeChecker $checker, int $value): void
+function doFoo(TypeChecker $checker, mixed $value): void
 {
 	if ($checker->isString($value)) {
-		echo 'impossible';
+		echo 'confirmed string';
 	}
 }
```
