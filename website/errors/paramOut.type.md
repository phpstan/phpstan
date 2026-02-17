---
title: "paramOut.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param-out int $p
 */
function foo(mixed &$p): void
{
	$p = 1;
	$p = 'str'; // ERROR: Parameter &$p @param-out type of function foo() expects int, string given.
}
```

## Why is it reported?

A by-reference parameter has a `@param-out` PHPDoc tag declaring the type that the parameter should have when the function returns. The value assigned to the parameter inside the function body does not match the declared `@param-out` type.

The `@param-out` tag is a contract with callers: it guarantees that after the function call, the referenced variable will have the specified type. Assigning a value of an incompatible type breaks this contract.

## How to fix it

Ensure the assigned value matches the declared `@param-out` type:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @param-out int $p
  */
 function foo(mixed &$p): void
 {
 	$p = 1;
-	$p = 'str';
+	$p = 2;
 }
```

Or update the `@param-out` type to reflect the actual values being assigned:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param-out int $p
+ * @param-out int|string $p
  */
 function foo(mixed &$p): void
 {
 	$p = 1;
 	$p = 'str';
 }
```
