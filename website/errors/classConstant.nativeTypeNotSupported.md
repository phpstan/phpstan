---
title: "classConstant.nativeTypeNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	const string VERSION = '1.0';
}
```

## Why is it reported?

Native types for class constants were introduced in PHP 8.3. Using typed class constants with an earlier PHP version results in a syntax error. PHPStan reports this when the configured PHP version does not support this feature.

## How to fix it

If upgrading to PHP 8.3 or later is not possible, remove the native type from the constant declaration:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	const string VERSION = '1.0';
+	const VERSION = '1.0';
 }
```

A PHPDoc type can be used instead to document the expected type without requiring PHP 8.3:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	const string VERSION = '1.0';
+	/** @var string */
+	const VERSION = '1.0';
 }
```
