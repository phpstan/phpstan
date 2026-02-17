---
title: "catch.internalTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

// In vendor/some-library/src/InternalTrait.php:
// namespace SomeLibrary;
// /** @internal */
// trait SomeInternalTrait {}

// In your code:
namespace App;

use SomeLibrary\SomeInternalTrait;

try {
    // ...
} catch (SomeInternalTrait $e) { // reported
    // ...
}
```

## Why is it reported?

A `catch` block references a trait that is marked as `@internal` by its library. Internal symbols are not meant to be used outside of the package that defines them. While catching a trait in a `catch` block is unusual and likely an error in itself, PHPStan specifically flags the usage of an internal trait in this context.

## How to fix it

Catch the appropriate exception class instead of referencing an internal trait.

```diff-php
 try {
     // ...
-} catch (SomeInternalTrait $e) {
+} catch (SomeException $e) {
     // ...
 }
```
