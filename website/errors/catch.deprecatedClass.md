---
title: "catch.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewException instead */
class OldException extends \Exception
{
}

try {
    // ...
} catch (OldException $e) {
    // ...
}
```

## Why is it reported?

The catch clause references a class that has been marked with a `@deprecated` PHPDoc tag. The class is scheduled for removal or replacement, and any usage of it -- including catching it as an exception -- should be migrated to the recommended alternative.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated class with its recommended replacement as indicated in the deprecation message:

```diff-php
 <?php declare(strict_types = 1);

 try {
     // ...
-} catch (OldException $e) {
+} catch (NewException $e) {
     // ...
 }
```
