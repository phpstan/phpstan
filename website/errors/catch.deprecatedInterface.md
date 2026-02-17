---
title: "catch.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewExceptionInterface instead */
interface OldExceptionInterface extends \Throwable
{
}

try {
    // ...
} catch (OldExceptionInterface $e) {
    // ...
}
```

## Why is it reported?

The catch clause references an interface that has been marked with a `@deprecated` PHPDoc tag. The interface is scheduled for removal or replacement, and any usage of it -- including catching exceptions by this interface type -- should be migrated to the recommended alternative.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated interface with its recommended replacement as indicated in the deprecation message:

```diff-php
 <?php declare(strict_types = 1);

 try {
     // ...
-} catch (OldExceptionInterface $e) {
+} catch (NewExceptionInterface $e) {
     // ...
 }
```
