---
title: "instanceof.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
class OldLogger {}

class NewLogger {}

function doFoo(object $obj): void
{
	if ($obj instanceof OldLogger) { // ERROR: Usage of deprecated class OldLogger in instanceof.
		// ...
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The class used in the `instanceof` check has been marked as deprecated with the `@deprecated` PHPDoc tag. Using deprecated classes should be avoided because they may be removed in a future version of the library or application. The deprecation notice typically suggests a replacement class to use instead.

## How to fix it

Replace the deprecated class with its suggested replacement:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(object $obj): void
 {
-	if ($obj instanceof OldLogger) {
+	if ($obj instanceof NewLogger) {
 		// ...
 	}
 }
```
