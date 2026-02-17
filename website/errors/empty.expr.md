---
title: "empty.expr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
	return 5;
}

if (empty(doFoo())) {
	echo 'empty';
}
```

## Why is it reported?

The expression inside `empty()` has a type that makes the result of `empty()` always predictable. In the example above, `doFoo()` always returns `int` `5`, which is truthy, so `empty(doFoo())` is always `false`. This makes the check redundant.

Depending on the expression's type, the message may say the expression "is always falsy", "is not falsy", "is always null", or "is not nullable".

## How to fix it

Remove the redundant `empty()` check:

```diff-php
 <?php declare(strict_types = 1);

-if (empty(doFoo())) {
-	echo 'empty';
-}
+$result = doFoo();
+if ($result === 0) {
+	echo 'empty';
+}
```

Or use a more explicit comparison that correctly reflects the intended logic.
