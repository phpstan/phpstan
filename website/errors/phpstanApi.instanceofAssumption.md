---
title: "phpstanApi.instanceofAssumption"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Type\Type;
use PHPStan\Type\StringType;

function doFoo(Type $type): void
{
	if ($type instanceof StringType) { // ERROR: Although StringType is covered by backward compatibility promise, this instanceof assumption might break because it's not guaranteed to always stay the same.
		// ...
	}
}
```

## Why is it reported?

This error is reported when an `instanceof` check is used against a PHPStan class that is covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise), but the specific `instanceof` assumption might break in a future minor version. While the class itself is part of the public API, PHPStan does not guarantee that a value currently represented by a specific class will always be represented by that same class.

For example, a type that is currently a `StringType` instance might be represented differently in a future version, even though the `StringType` class itself still exists.

## How to fix it

Instead of checking for a specific class with `instanceof`, use the type's API methods to query its properties:

```diff-php
 <?php declare(strict_types = 1);

 use PHPStan\Type\Type;
-use PHPStan\Type\StringType;

 function doFoo(Type $type): void
 {
-	if ($type instanceof StringType) {
+	if ($type->isString()->yes()) {
 		// ...
 	}
 }
```

If you believe the `instanceof` check is the correct approach and should be supported, open a discussion at [github.com/phpstan/phpstan/discussions](https://github.com/phpstan/phpstan/discussions).

See also: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
