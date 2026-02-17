---
title: "varTag.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): int
{
    $value = 42;

    /** @var string $value */

    return $value;
}
```

## Why is it reported?

The type declared in the `@var` PHPDoc tag is not a subtype of the type that PHPStan has determined for the expression. The `@var` tag claims the variable has a specific type, but PHPStan's analysis of the code shows a different, incompatible type.

In the example, PHPStan knows that `$value` is `int` (value `42`), but the `@var` tag declares it as `string`. Since `string` is not a subtype of `int`, the tag is incorrect.

This check helps catch incorrect `@var` annotations that could mask real type errors.

## How to fix it

Remove the incorrect `@var` tag if PHPStan already knows the correct type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): int
 {
     $value = 42;

-    /** @var string $value */
-
     return $value;
 }
```

Or correct the `@var` tag to match the actual type of the expression:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): int
 {
     $value = 42;

-    /** @var string $value */
+    /** @var int $value */

     return $value;
 }
```
