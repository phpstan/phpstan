---
title: "new.nonObject"
shortDescription: "Instantiating a class from an expression that is neither a class-string nor an object."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $int): void
{
	new $int;
}
```

## Why is it reported?

The dynamic instantiation syntax `new $expr` requires `$expr` to be either a `string` holding a class name or an object whose class should be instantiated. When `$expr` is any other type — such as `int`, `float`, `bool`, `array`, or a union that includes those types — PHP throws a fatal `Error` at runtime:

```
Uncaught Error: Class name must be a valid object or a string
```

A nullable string (`string|null`) is also reported, because passing `null` triggers the same fatal error.

## How to fix it

Make sure the expression used after `new` resolves to a class name or an object. Narrow the type so the non-object cases are excluded:

```diff-php
-function doFoo(int $int): void
+function doFoo(string $class): void
 {
-	new $int;
+	new $class;
 }
```

When the value can legitimately hold several types, [narrow the type](/writing-php-code/narrowing-types) before instantiating:

```diff-php
 /** @param int|string $intOrString */
 function doFoo($intOrString): void
 {
+	if (!is_string($intOrString)) {
+		return;
+	}
 	new $intOrString;
 }
```

For a parameter that is meant to receive a class name, use the [`class-string`](/writing-php-code/phpdoc-types#class-string) type so PHPStan and other developers know only class names are accepted:

```diff-php
-/** @param string $class */
+/** @param class-string $class */
 function doFoo(string $class): void
 {
 	new $class;
 }
```
