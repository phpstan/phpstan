---
title: "argument.byRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function increment(int &$value): void
{
	$value++;
}

function doFoo(): void
{
	increment(rand());
}
```

## Why is it reported?

When a function parameter is declared with `&` (pass by reference), PHP requires the caller to pass a variable, array element, or property -- something that can be written back to. Passing an expression such as a function call result, a literal, or a `null` is not valid because there is no storage location for PHP to write the modified value into. Similarly, a readonly or `@readonly` property cannot be passed by reference because it must not be modified after initialisation.

## How to fix it

Store the value in a variable before passing it by reference:

```diff-php
 <?php declare(strict_types = 1);

 function increment(int &$value): void
 {
 	$value++;
 }

 function doFoo(): void
 {
-	increment(rand());
+	$number = rand();
+	increment($number);
 }
```

Or, if the function does not actually need to modify the argument, remove the `&` from the parameter declaration:

```diff-php
 <?php declare(strict_types = 1);

-function increment(int &$value): void
+function increment(int $value): int
 {
-	$value++;
+	return $value + 1;
 }
```
