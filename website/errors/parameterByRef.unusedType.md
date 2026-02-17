---
title: "parameterByRef.unusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int|string &$value): void
{
	$value = 42;
}
```

## Why is it reported?

A by-reference parameter has a union type, but the function never assigns one of the union members to it. In the example above, `$value` is typed as `int|string`, but the function only ever assigns an `int`. The `string` part of the union is never used and can be removed.

## How to fix it

Narrow the type to only include what is actually assigned:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int|string &$value): void
+function doFoo(int &$value): void
 {
 	$value = 42;
 }
```

You can also narrow the parameter out type with a `@param-out` PHPDoc tag if you cannot change the native type.
