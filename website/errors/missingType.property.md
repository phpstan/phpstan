---
title: "missingType.property"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class MyClass
{
	private $value;
}
```

## Why is it reported?

The property has no type specified -- neither a native PHP type declaration nor a `@var` PHPDoc tag. Without a type, PHPStan cannot verify that the property is used correctly throughout the codebase. The property is implicitly typed as `mixed`, which disables many checks.

## How to fix it

Add a native type declaration (PHP 7.4+):

```diff-php
 class MyClass
 {
-	private $value;
+	private string $value;
 }
```

For older PHP versions, add a `@var` PHPDoc tag:

```diff-php
 class MyClass
 {
+	/** @var string */
 	private $value;
 }
```
