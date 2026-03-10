---
title: "return.tooWideBool"
shortDescription: "Function declares bool return type but only ever returns true or only false."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @return bool */
function alwaysTrue(): bool
{
	if (rand(0, 1)) {
		return true;
	}

	return true;
}
```

## Why is it reported?

The function or method declares `bool` as its return type, but PHPStan has determined that only one of the two boolean values (`true` or `false`) is ever actually returned. The return type is wider than necessary because one of the boolean values is never returned.

In the example above, every code path returns `true`, so the return type should be narrowed from `bool` to `true`.

## How to fix it

Narrow the return type to the specific boolean literal type that is actually returned (PHP 8.2+):

```diff-php
-function alwaysTrue(): bool
+function alwaysTrue(): true
 {
 	if (rand(0, 1)) {
 		return true;
 	}

 	return true;
 }
```

On older PHP versions, the `true` standalone type is not available natively. Use a PHPDoc `@return` tag instead:

```diff-php
-/** @return bool */
+/** @return true */
 function alwaysTrue(): bool
```

Learn more: [PHPDoc Types](/writing-php-code/phpdoc-types)

If the function is a non-private method that overrides a parent method, the return type may need to stay as `bool` for compatibility. In that case, configure PHPStan to check protected and public methods using the [`checkTooWideReturnTypesInProtectedAndPublicMethods`](/config-reference#checktoowidereturntypesinprotectedandpublicmethods) option.
