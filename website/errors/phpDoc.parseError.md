---
title: "phpDoc.parseError"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param array<int, > $values
 */
function doFoo(array $values): void
{
}
```

## Why is it reported?

The PHPDoc tag contains a value that could not be parsed. In the example above, the `@param` tag has an incomplete generic type `array<int, >` (missing the value type after the comma).

## How to fix it

Fix the PHPDoc syntax:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @param array<int, > $values
+ * @param array<int, string> $values
  */
 function doFoo(array $values): void
 {
 }
```
