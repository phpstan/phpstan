---
title: "empty.expr"
shortDescription: "Result of empty() on this expression is always predictable."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @return positive-int */
function getCount(): int
{
	return 1;
}

if (empty(getCount())) {
	echo 'empty';
}
```

## Why is it reported?

The expression inside `empty()` has a type that makes the result of `empty()` always predictable. In the example above, `getCount()` always returns a `positive-int`, which is always truthy, so `empty(getCount())` is always `false`. This makes the check redundant.

Depending on the expression's type, the message may say the expression "is always falsy", "is not falsy", "is always null", or "is not nullable".

## How to fix it

Remove the redundant `empty()` check since the value can never be empty:

```diff-php
 <?php declare(strict_types = 1);

-if (empty(getCount())) {
-	echo 'empty';
-}
+echo getCount();
```

Or if the type is incorrect, fix the return type to allow falsy values:

```diff-php
 <?php declare(strict_types = 1);

-/** @return positive-int */
+/** @return non-negative-int */
 function getCount(): int
 {
 	return 1;
 }
```
