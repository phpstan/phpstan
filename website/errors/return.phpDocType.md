---
title: "return.phpDocType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return string
 */
function doFoo(): int
{
	return 1;
}
```

## Why is it reported?

The `@return` PHPDoc tag specifies a type that is incompatible with the native return type. In the example above, the PHPDoc says the function returns `string`, but the native return type is `int`. These types are incompatible.

## How to fix it

Align the PHPDoc type with the native return type:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @return string
+ * @return int
  */
 function doFoo(): int
 {
 	return 1;
 }
```
