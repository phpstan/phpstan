---
title: "cast.int"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = (int) new \stdClass();
```

## Why is it reported?

The expression being cast to `int` is of a type that cannot be converted to an integer. PHP does not support casting certain types (such as objects) to `int`, and attempting to do so will result in an error at runtime.

In the example above, an `stdClass` object cannot be cast to `int`.

## How to fix it

Extract the numeric value from the object or expression before casting:

```diff-php
 <?php declare(strict_types = 1);

-$value = (int) new \stdClass();
+$obj = new \stdClass();
+$obj->value = 42;
+$value = (int) $obj->value;
```

Or ensure the expression produces a type that can be cast to `int`, such as `string`, `float`, or `bool`:

```diff-php
 <?php declare(strict_types = 1);

-$value = (int) new \stdClass();
+$value = (int) '42';
```
