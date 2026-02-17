---
title: "catch.neverThrown"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

try {
    echo 'Hello';
} catch (\DomainException $e) { // reported: Dead catch - DomainException is never thrown in the try block.
    // ...
}
```

## Why is it reported?

The caught exception type is never thrown by any of the code in the corresponding `try` block. This means the `catch` block is dead code that will never execute. Having dead `catch` blocks makes the code harder to understand and can hide missing error handling for exceptions that are actually thrown.

PHPStan performs [precise try-catch-finally analysis](https://phpstan.org/blog/precise-try-catch-finally-analysis) to determine which exceptions can be thrown inside the `try` block. It relies on `@throws` PHPDoc tags on called functions and methods to know which exceptions they throw. You can learn more about this in [Bring Your Exceptions Under Control](https://phpstan.org/blog/bring-your-exceptions-under-control).

Sometimes this error is a false positive because the code is not documented sufficiently — the called function or method actually does throw the exception, but it's missing the `@throws` tag. In that case, the fix is to add the correct `@throws` PHPDoc tag above the called function or method.

## How to fix it

If the `catch` block is truly dead code, remove it or fix the `try` block to contain code that actually throws the caught exception type.

Removing the dead catch:

```diff-php
-try {
     echo 'Hello';
-} catch (\DomainException $e) {
-    // ...
-}
```

Or catching an exception that is actually thrown:

```diff-php
 try {
-    echo 'Hello';
+    $value = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
-} catch (\DomainException $e) {
+} catch (\JsonException $e) {
     // ...
 }
```

If the called function or method does throw the exception but is missing the `@throws` tag, add it:

```diff-php
+/** @throws \DomainException */
 function doSomething(): void
 {
     // ...
 }
```

You can also use an inline `/** @throws \DomainException */` PHPDoc above a statement in the `try` block:

```diff-php
 try {
+    /** @throws \DomainException */
     doSomething();
 } catch (\DomainException $e) {
     // ...
 }
```
