---
title: "missingType.parameter"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function processData($value): bool
{
	return $value !== null;
}
```

## Why is it reported?

A function or method parameter has no type specified, either as a native type declaration or a PHPDoc `@param` tag. Without a type, PHPStan treats the parameter as `mixed` and cannot perform accurate type checking on how it is used within the function or what values callers pass to it.

## How to fix it

Add a native type declaration to the parameter:

```diff-php
 <?php declare(strict_types = 1);

-function processData($value): bool
+function processData(mixed $value): bool
 {
 	return $value !== null;
 }
```

Or add a PHPDoc `@param` tag with a more specific type:

```diff-php
 <?php declare(strict_types = 1);

+/** @param string|int $value */
 function processData($value): bool
 {
 	return $value !== null;
 }
```
