---
title: "classConstant.dynamicString"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(string $className): void
{
	echo $className::class;
}
```

## Why is it reported?

Accessing `::class` on a dynamic string variable is not supported in PHP. While PHP 8.0 introduced `$object::class` for objects, this does not work for string variables containing class names.

## How to fix it

Use the string variable directly, as it already contains the class name:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(string $className): void
 {
-	echo $className::class;
+	echo $className;
 }
```
