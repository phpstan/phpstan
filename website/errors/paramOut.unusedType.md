---
title: "paramOut.unusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param-out int|string $result
 */
function compute(mixed &$result): void
{
	$result = 42;
}
```

## Why is it reported?

The `@param-out` type annotation declares a union type for a by-reference parameter, but one of the types in the union is never actually assigned to the parameter. PHPStan analyzes all code paths in the function and determines that a part of the declared output type is too wide because it is never used. This indicates that the `@param-out` annotation is more permissive than what the function actually produces.

## How to fix it

Narrow the `@param-out` type to only include the types that are actually assigned to the parameter.

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param-out int|string $result
+ * @param-out int $result
  */
 function compute(mixed &$result): void
 {
 	$result = 42;
 }
```
