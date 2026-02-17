---
title: "catch.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewException instead */
trait OldExceptionTrait
{
}

class MyException extends \Exception
{
	use OldExceptionTrait;
}

try {
	// ...
} catch (MyException $e) {
	// The catch block itself is fine, but the trait is deprecated
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

The `catch` block references a type that uses a deprecated trait. The trait has been marked with a `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement. Any usage of deprecated types should be migrated to the recommended alternative.

## How to fix it

Replace the deprecated trait with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 class MyException extends \Exception
 {
-	use OldExceptionTrait;
+	use NewExceptionTrait;
 }
```

Or if the deprecation affects the caught class directly, catch the replacement class instead:

```diff-php
 try {
 	// ...
-} catch (MyException $e) {
+} catch (NewException $e) {
 	// ...
 }
```
