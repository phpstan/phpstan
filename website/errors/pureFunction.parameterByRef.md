---
title: "pureFunction.parameterByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function increment(int &$value): int // ERROR: Function increment() is marked as pure but parameter $value is passed by reference.
{
	$value++;
	return $value;
}
```

## Why is it reported?

A function marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. A parameter passed by reference (`&$value`) allows the function to modify the caller's variable, which is a side effect. This contradicts the definition of a pure function.

Even if the function does not actually modify the referenced parameter, the mere presence of a by-reference parameter signals that the function's contract allows mutation, which is incompatible with purity.

## How to fix it

Remove the by-reference parameter and return the computed value instead:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function increment(int &$value): int
+function increment(int $value): int
 {
-	$value++;
-	return $value;
+	return $value + 1;
 }
```

If the function needs to modify the caller's variable, it is not pure. Remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function increment(int &$value): int
 {
 	$value++;
 	return $value;
 }
```
