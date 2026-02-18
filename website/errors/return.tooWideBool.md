---
title: "return.tooWideBool"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function isPositive(int $number): bool
{
	return $number > 0;
	// always returns true or false, but never actually returns false
	// if the function only ever returns true
}

function alwaysTrue(): bool
{
	return true;
}
```

## Why is it reported?

The function or method declares `bool` as its return type, but PHPStan has determined that only one of the two boolean values (`true` or `false`) is ever actually returned. The return type is wider than necessary because one of the boolean values is never returned.

For example, if a function always returns `true`, the return type should be narrowed to `true` instead of `bool`.

## How to fix it

Narrow the return type to the specific boolean literal type that is actually returned.

```diff-php
-function alwaysTrue(): bool
+function alwaysTrue(): true
 {
 	return true;
 }
```

If the function is a non-private method that overrides a parent method, the return type may need to stay as `bool` for compatibility. In that case, configure PHPStan to check protected and public methods using the [`checkTooWideReturnTypesInProtectedAndPublicMethods`](/config-reference#checktoowidereturntypesinprotectedandpublicmethods) option.
