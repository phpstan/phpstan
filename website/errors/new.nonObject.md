---
title: "new.nonObject"
shortDescription: "Instantiating a dynamic class name whose value is neither a class-string nor an object."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $int): void
{
	$object = new $int;
}
```

## Why is it reported?

When you instantiate a dynamic class name with `new $expr`, the expression must evaluate to either a `string` (the class name) or an existing object whose class is reused. PHP throws a fatal error if the value is anything else — an `int`, `float`, `bool`, `array`, `null`, or a union that includes such types.

PHPStan reports this whenever the type of the expression after `new` is not a `class-string` (or general `string`) and not an `object`.

## How to fix it

Make sure the expression holds a class name string or an object. Narrow the parameter or variable to the correct type:

```diff-php
-function doFoo(int $int): void
+function doFoo(string $className): void
 {
-	$object = new $int;
+	$object = new $className;
 }
```

For stricter guarantees, type the value as a [`class-string`](/writing-php-code/phpdoc-types#class-string), which documents that the string must be a valid class name:

```diff-php
+/**
+ * @param class-string $className
+ */
 function doFoo(string $className): void
 {
 	$object = new $className;
 }
```

If the value comes from a union type, use [type narrowing](/writing-php-code/narrowing-types) to ensure only the string or object case reaches the `new` expression.
