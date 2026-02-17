---
title: "cast.double"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = (float) new \stdClass();
```

## Why is it reported?

The expression being cast to `float` is of a type that cannot be converted to a float. PHP does not support casting certain types (such as objects that do not implement `__toString()` or other non-numeric types) to `float`, and attempting to do so will result in an error at runtime.

In the example above, an `stdClass` object cannot be cast to `float`.

## How to fix it

Extract the numeric value from the object or expression before casting:

```diff-php
 <?php declare(strict_types = 1);

-$value = (float) new \stdClass();
+$obj = new \stdClass();
+$obj->value = 3.14;
+$value = (float) $obj->value;
```

Or ensure the expression produces a type that can be cast to `float`, such as `int`, `string`, or `bool`:

```diff-php
 <?php declare(strict_types = 1);

-$value = (float) new \stdClass();
+$value = (float) '3.14';
```
