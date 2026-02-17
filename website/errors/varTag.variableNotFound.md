---
title: "varTag.variableNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	/** @var int $foo */
	echo 'hello';
}
```

## Why is it reported?

The `@var` PHPDoc tag specifies a variable name that does not exist in the current scope. The variable referenced in the tag was never defined before this point in the code and is not being assigned on the line below the tag. This usually indicates a typo in the variable name or a leftover annotation from refactored code.

This is also reported when a `@var` tag above a multi-variable assignment references a variable that does not exist and is not one of the variables being assigned:

```php
<?php declare(strict_types = 1);

function doBar(string $a): void
{
	/**
	 * @var string $a
	 * @var string $b
	 */
	echo $a;
}
```

In this example, `$b` does not exist in the current scope.

## How to fix it

Correct the variable name to match an existing variable in the current scope:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
+	$test = someFunction();
-	/** @var int $foo */
-	echo 'hello';
+	/** @var int $test */
+	echo $test;
 }
```

Or remove the `@var` tag if it is no longer needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	/** @var int $foo */
 	echo 'hello';
 }
```
