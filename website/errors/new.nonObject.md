---
title: "new.nonObject"
shortDescription: "Instantiating a value that is neither a class-name string nor an object."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value): void
{
	$object = new $value;
}
```

## Why is it reported?

The `new` operator accepts either a class name (as a string) or an object whose class should be instantiated. Here the expression has type `int`, which is neither. At runtime PHP throws an `Error` because it cannot resolve a class from a non-string, non-object value.

This typically happens when a variable holds the wrong type — for example an integer or array where a [class-string](/writing-php-code/phpdoc-types#class-string) was expected.

This check is part of [bleeding edge](/blog/what-is-bleeding-edge) and is enabled by including `bleedingEdge.neon` in your configuration.

## How to fix it

Pass a class-name string or an object to `new`. If the variable is meant to hold a class name, type it as a `class-string`:

```diff-php
-function doFoo(int $value): void
+/**
+ * @param class-string $value
+ */
+function doFoo(string $value): void
 {
 	$object = new $value;
 }
```

A variable that holds an object is also valid — `new $object` creates another instance of the same class. The error only appears for scalar types, arrays, and other non-instantiable values, so make sure the variable cannot hold those:

```diff-php
-function doFoo(int|object $value): void
+function doFoo(object $value): void
 {
 	$object = new $value;
 }
```
