---
title: "varTag.nativeType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function process(): int
{
	/** @var string $result */
	$result = compute();

	return $result;
}

function compute(): int
{
	return 42;
}
```

## Why is it reported?

The type specified in the `@var` PHPDoc tag is not a subtype of the expression's native type. The `@var` tag claims the value is of a type that contradicts what PHP itself determines the type to be. This indicates the `@var` annotation is incorrect and could mask real type errors.

## How to fix it

Remove the incorrect `@var` tag or correct it to match the actual native type.

```diff-php
 <?php declare(strict_types = 1);

 function process(): int
 {
-	/** @var string $result */
 	$result = compute();

 	return $result;
 }
```

If you need a more specific type than what PHP can express natively, make sure it is a subtype of the native type:

```diff-php
 <?php declare(strict_types = 1);

 function process(): int
 {
-	/** @var string $result */
+	/** @var positive-int $result */
 	$result = compute();

 	return $result;
 }
```
