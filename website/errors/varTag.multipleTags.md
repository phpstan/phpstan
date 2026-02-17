---
title: "varTag.multipleTags"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	/**
	 * @var int
	 * @var string
	 */
	$test = someFunction();
}
```

## Why is it reported?

Multiple `@var` PHPDoc tags are placed above a single variable assignment. When a single variable is being assigned, PHPStan expects at most one `@var` tag (optionally with `@phpstan-var` or `@psalm-var` prefixed variants). Having multiple conflicting `@var` tags is ambiguous and PHPStan cannot determine which one should apply.

## How to fix it

Use a single `@var` tag with the correct type:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	/**
-	 * @var int
-	 * @var string
-	 */
+	/** @var int|string */
 	$test = someFunction();
 }
```

If you need a more specific type than what PHPStan infers, use `@phpstan-var` alongside `@var`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	/**
-	 * @var int
-	 * @var string
-	 */
+	/**
+	 * @var int|string
+	 * @phpstan-var positive-int|non-empty-string
+	 */
 	$test = someFunction();
 }
```
