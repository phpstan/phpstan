---
title: "possiblyImpure.propertyAssign"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public string $name = '';
}

/**
 * @phpstan-pure
 */
function doFoo(object $obj): string
{
	$obj->foo = 'test';

	return 'result';
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` is expected to have no side effects -- it should only compute and return a value without modifying any external state. Assigning a value to an object property is a side effect because it modifies the state of the object.

When PHPStan cannot determine with certainty whether the property assignment occurs (for example, because of conditional logic or the object type being imprecise), it reports a "possibly impure" property assignment rather than a definite impure one.

## How to fix it

If the function genuinely needs to assign a property, it is not pure. Remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function doFoo(object $obj): string
 {
 	$obj->foo = 'test';

 	return 'result';
 }
```

If the function should remain pure, remove the property assignment and restructure the code so that the caller handles the side effect:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function doFoo(object $obj): string
+function doFoo(): string
 {
-	$obj->foo = 'test';
-
 	return 'result';
 }
```
