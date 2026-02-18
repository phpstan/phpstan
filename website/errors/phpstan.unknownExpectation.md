---
title: "phpstan.unknownExpectation"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$type = rand(0, 1) ? 'int' : 'string';
assertType($type, $value);
```

## Why is it reported?

PHPStan's test assertion functions (`assertType`, `assertNativeType`, `assertSuperType`, `assertVariableCertainty`) require the expected type to be a literal string so that the expectation is unambiguous. When the first argument is not a constant literal string, PHPStan cannot determine what type was expected.

This also applies to `assertVariableCertainty`, where the first argument must be a static call to `TrinaryLogic::createYes()`, `TrinaryLogic::createMaybe()`, or `TrinaryLogic::createNo()`.

## How to fix it

Pass a literal string as the first argument:

```diff-php
 use function PHPStan\Testing\assertType;

-$type = rand(0, 1) ? 'int' : 'string';
-assertType($type, $value);
+assertType('int', $value);
```

For `assertVariableCertainty`, use a direct `TrinaryLogic` static call:

```diff-php
 use function PHPStan\Testing\assertVariableCertainty;
 use PHPStan\TrinaryLogic;

-assertVariableCertainty($certainty, $variable);
+assertVariableCertainty(TrinaryLogic::createYes(), $variable);
```
