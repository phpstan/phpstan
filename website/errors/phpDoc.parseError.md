---
title: "phpDoc.parseError"
shortDescription: "PHPDoc tag contains a value that could not be parsed."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param int|& $value
 */
function doFoo($value): void
{
}
```

## Why is it reported?

The PHPDoc tag contains a value that could not be parsed. In the example above, the `@param` tag has an invalid type `int|&` -- the `&` is not a valid type and cannot appear after the union operator `|`.

## How to fix it

Fix the PHPDoc syntax:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param int|& $value
+ * @param int $value
  */
-function doFoo($value): void
+function doFoo(int $value): void
 {
 }
```
