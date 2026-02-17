---
title: "postInc.type"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = new \stdClass();
$value++; // ERROR: Cannot use ++ on stdClass.
```

## Why is it reported?

The post-increment operator (`$value++`) is only valid for numeric types (int, float), strings, and in some PHP versions null and bool. Attempting to increment a value of a type that does not support this operation will either produce unexpected results or trigger a deprecation/error depending on the PHP version.

## How to fix it

Ensure the variable has a type that supports the increment operator:

```diff-php
 <?php declare(strict_types = 1);

-$value = new \stdClass();
-$value++;
+$value = 0;
+$value++;
```

Or perform the arithmetic explicitly on a supported type:

```diff-php
 <?php declare(strict_types = 1);

-$value = new \stdClass();
-$value++;
+$value = 1;
+$value = $value + 1;
```
