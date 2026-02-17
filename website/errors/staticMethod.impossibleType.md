---
title: "staticMethod.impossibleType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class TypeChecker
{
    /**
     * @phpstan-assert-if-true int $value
     */
    public static function isInt(mixed $value): bool
    {
        return is_int($value);
    }
}

function doFoo(string $value): void
{
    if (TypeChecker::isInt($value)) {
        // never reached - a string is never an int
    }
}
```

## Why is it reported?

The call to a static method that performs a type check will always evaluate to `false` because the type of the argument makes it impossible for the check to succeed. PHPStan understands type-checking methods through `@phpstan-assert-if-true` and `@phpstan-assert-if-false` PHPDoc tags, as well as methods configured as type-specifying extensions.

In this example, `TypeChecker::isInt()` asserts that the value is `int`, but the argument is always `string`, so the check can never return `true`.

## How to fix it

Pass the correct variable or value to the type check:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(string $value): void
+function doFoo(string|int $value): void
 {
     if (TypeChecker::isInt($value)) {
         // now this branch can be reached
     }
 }
```

Or remove the impossible check if it is not needed:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(string $value): void
 {
-    if (TypeChecker::isInt($value)) {
-        // never reached
-    }
+    // handle string directly
 }
```
