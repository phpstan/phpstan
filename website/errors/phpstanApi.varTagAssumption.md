---
title: "phpstanApi.varTagAssumption"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Type\Type;
use PHPStan\Type\StringType;

function doFoo(Type $type): void
{
	/** @var StringType $type */
	$type->describe(\PHPStan\Type\VerbosityLevel::value());
}
```

## Why is it reported?

A `@var` PHPDoc tag is used to narrow the type of an expression to a specific PHPStan type class. While the PHPStan class referenced in the `@var` tag may be covered by the [backward compatibility promise](https://phpstan.org/developing-extensions/backward-compatibility-promise), assuming that a value currently represented by one type class will always be that specific class is error-prone and dangerous.

PHPStan's internal type representation can change between versions. A value that is currently a `StringType` instance might be represented differently in a future version, even if the `StringType` class itself still exists.

## How to fix it

Instead of using `@var` to assume a specific PHPStan type class, use the type's API methods to query its properties:

```diff-php
 <?php declare(strict_types = 1);

 use PHPStan\Type\Type;
-use PHPStan\Type\StringType;

 function doFoo(Type $type): void
 {
-	/** @var StringType $type */
-	$type->describe(\PHPStan\Type\VerbosityLevel::value());
+	if ($type->isString()->yes()) {
+		$type->describe(\PHPStan\Type\VerbosityLevel::value());
+	}
 }
```

See also: [Backward Compatibility Promise](https://phpstan.org/developing-extensions/backward-compatibility-promise)
