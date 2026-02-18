---
title: "staticMethod.alreadyNarrowedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class TypeChecker
{
	/**
	 * @phpstan-assert int $value
	 */
	public static function assertInt(mixed $value): void
	{
	}
}

function doFoo(int $value): void
{
	TypeChecker::assertInt($value);
}
```

## Why is it reported?

A static method call that performs a type check (such as an assertion) will always evaluate to true because the argument is already known to be of the asserted type. The check is redundant -- the type has already been narrowed before the call.

This indicates either unnecessary code, or a logic error where a different variable was intended.

## How to fix it

Remove the redundant type check if the type is already guaranteed:

```diff-php
 function doFoo(int $value): void
 {
-	TypeChecker::assertInt($value);
 	// $value is already int
 }
```

Or check a different variable that actually needs the assertion:

```diff-php
-function doFoo(int $value): void
+function doFoo(mixed $value): void
 {
 	TypeChecker::assertInt($value);
 }
```

If the static method call is the last condition in a series of conditions, and the tip `Remove remaining cases below this one and this error will disappear too.` is shown, consider restructuring the conditional logic.
