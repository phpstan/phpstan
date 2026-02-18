---
title: "phpstan.type"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

// In a PHPStan test file:

use function PHPStan\Testing\assertType;

assertType('int', 1 + 1);
```

## Why is it reported?

The `PHPStan\Testing\assertType()` function is used in PHPStan's internal test suite to verify that the inferred type of an expression matches the expected type string. When the actual type does not match the expected type, this error is reported.

This identifier is part of PHPStan's testing infrastructure and is not encountered during normal usage.

## How to fix it

Update the expected type string to match the actual inferred type of the expression, or fix the code so the expression produces the expected type.
