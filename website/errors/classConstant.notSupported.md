---
title: "classConstant.notSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function getClass(object $obj): string
{
	return $obj::class;
}
```

## Why is it reported?

Accessing `::class` on an expression (such as a variable) is only supported on PHP 8.0 and later. If your project targets an earlier PHP version, this syntax will cause a fatal error.

## How to fix it

Use `get_class()` instead:

```diff-php
 <?php declare(strict_types = 1);

 function getClass(object $obj): string
 {
-	return $obj::class;
+	return get_class($obj);
 }
```

Or update the PHP version requirement for your project to PHP 8.0 or later.
