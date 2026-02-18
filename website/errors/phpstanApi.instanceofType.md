---
title: "phpstanApi.instanceofType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Type\StringType;
use PHPStan\Type\Type;

function processType(Type $type): void
{
	if ($type instanceof StringType) {
		// ...
	}
}
```

## Why is it reported?

Using `instanceof` checks against specific PHPStan `Type` classes is error-prone and deprecated. PHPStan's type system uses composed types (unions, intersections, constant types), so checking for a specific class often misses valid cases. For example, a `ConstantStringType` is a string type, but `$type instanceof StringType` would be `false`.

The PHPStan `Type` interface provides dedicated methods that correctly handle all type compositions.

Learn more: [Why Is instanceof *Type Wrong and Getting Deprecated](/blog/why-is-instanceof-type-wrong-and-getting-deprecated)

## How to fix it

Use the recommended `Type` interface method instead of `instanceof`:

```diff-php
 use PHPStan\Type\Type;

 function processType(Type $type): void
 {
-	if ($type instanceof StringType) {
+	if ($type->isString()->yes()) {
 		// ...
 	}
 }
```

Consult the error message for which method to use. Common replacements include:

- `instanceof StringType` -- use `Type::isString()`
- `instanceof IntegerType` -- use `Type::isInteger()`
- `instanceof ArrayType` -- use `Type::isArray()` or `Type::getArrays()`
- `instanceof NullType` -- use `Type::isNull()`
- `instanceof ObjectType` -- use `Type::isObject()` or `Type::getObjectClassNames()`
- `instanceof ConstantStringType` -- use `Type::getConstantStrings()`
- `instanceof BooleanType` -- use `Type::isBoolean()`
