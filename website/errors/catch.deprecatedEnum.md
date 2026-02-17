---
title: "catch.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewException instead. */
enum AppError: string implements \Throwable
{
    // ...
}

try {
    // ...
} catch (AppError $e) {
    // ...
}
```

## Why is it reported?

The catch clause references an enum that has been marked with a `@deprecated` PHPDoc tag. The enum is scheduled for removal or replacement, and any usage of it -- including catching it -- should be migrated to the recommended alternative.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated enum with its recommended replacement as indicated in the deprecation message:

```diff-php
 <?php declare(strict_types = 1);

 try {
     // ...
-} catch (AppError $e) {
+} catch (NewException $e) {
     // ...
 }
```
