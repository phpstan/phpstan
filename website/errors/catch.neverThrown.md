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

## How to fix it

Remove the unnecessary `catch` block, or fix the `try` block to contain code that actually throws the caught exception type.

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
