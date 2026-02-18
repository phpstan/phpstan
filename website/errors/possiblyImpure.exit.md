---
title: "possiblyImpure.exit"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function getValue(int $input): int
{
	$closure = function () {
		exit();
	};

	$closure();

	return $input * 2;
}
```

## Why is it reported?

The function or method is marked as `@phpstan-pure` but may call `exit` or `die`. These constructs terminate the entire PHP process, which is a side effect. Pure functions must not have side effects; they should only compute and return a value based on their inputs.

## How to fix it

Remove the `exit`/`die` call from the pure function, or remove the `@phpstan-pure` annotation if process termination is intentional:

```diff-php
 /**
- * @phpstan-pure
+ * @phpstan-impure
  */
 function getValue(int $input): int
 {
 	$closure = function () {
 		exit();
 	};

 	$closure();

 	return $input * 2;
 }
```
