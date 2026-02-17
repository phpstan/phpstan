---
title: "smallerOrEqual.invalid"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function check(stdClass $object, int $number): bool
{
    return $object <= $number;
}
```

## Why is it reported?

The `<=` comparison operator is used between types that cannot be meaningfully compared. Comparing an object with a number produces unreliable results and is almost always a bug. PHP will attempt type juggling but the outcome is not well-defined for these type combinations.

## How to fix it

Extract a comparable value from the object before comparing:

```diff-php
 <?php declare(strict_types = 1);

-function check(stdClass $object, int $number): bool
+function check(stdClass $object, int $number, int $value): bool
 {
-    return $object <= $number;
+    return $value <= $number;
 }
```
