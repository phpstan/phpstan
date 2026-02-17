---
title: "empty.notAllowed"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function isValid(string $name): bool
{
	return !empty($name);
}
```

This rule is provided by the package [`phpstan/phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules).

## Why is it reported?

The `empty()` language construct is disallowed by strict rules because it combines two checks into one -- it checks if a variable is set and if its value is falsy. This makes its behaviour unpredictable with different types: `empty('0')` returns `true`, `empty(0)` returns `true`, and `empty([])` returns `true`. These implicit coercions can hide bugs.

Using explicit comparisons makes the code's intent clearer and avoids surprising results from PHP's loose type juggling.

## How to fix it

Replace `empty()` with an explicit comparison appropriate for the expected type:

```diff-php
 <?php declare(strict_types = 1);

 function isValid(string $name): bool
 {
-	return !empty($name);
+	return $name !== '';
 }
```

For arrays, compare against an empty array or use `count()`:

```diff-php
 <?php declare(strict_types = 1);

 /** @param list<string> $items */
 function hasItems(array $items): bool
 {
-	return !empty($items);
+	return count($items) > 0;
 }
```
