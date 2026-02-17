---
title: "instanceof.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelperInterface instead */
trait OldHelperTrait
{
	public function help(): void {}
}

function doFoo(object $obj): void
{
	if ($obj instanceof OldHelperTrait) { // ERROR: Instanceof references deprecated trait OldHelperTrait.
		// ...
	}
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The `instanceof` check references a trait that has been marked with the `@deprecated` PHPDoc tag. Deprecated traits are scheduled for removal or replacement, and any usage -- including in `instanceof` checks -- should be migrated to the recommended alternative. Additionally, `instanceof` checks against traits always evaluate to false at runtime because PHP does not support trait-based type checks.

## How to fix it

Replace the deprecated trait with the recommended replacement type in the `instanceof` check:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(object $obj): void
 {
-	if ($obj instanceof OldHelperTrait) {
+	if ($obj instanceof NewHelperInterface) {
 		// ...
 	}
 }
```
