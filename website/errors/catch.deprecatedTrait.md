---
title: "catch.deprecatedTrait"
shortDescription: "Catch block references a deprecated trait."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewException instead */
trait OldTrait
{
}

try {
	throw new \Exception();
} catch (OldTrait $e) {
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `catch` block references a trait that has been marked as `@deprecated`. Deprecated traits are planned for removal in a future version. Since traits cannot be thrown or caught in PHP, this code is already incorrect, and the deprecated trait reference compounds the problem.

## How to fix it

Catch a proper exception class instead of a deprecated trait:

```diff-php
 try {
 	throw new \Exception();
-} catch (OldTrait $e) {
+} catch (\Exception $e) {
 }
```
