---
title: "array.invalidKey"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$key = new stdClass();
$array = [$key => 'value'];
```

## Why is it reported?

The value used as an array key is not a valid array key type. PHP only allows `int`, `string`, `bool`, `float`, and `null` as array keys (with `bool`, `float`, and `null` being cast to `int` or `string`). Using any other type, such as an object or an array, as an array key results in a fatal error at runtime.

In the example above, an `stdClass` object is used as an array key, which is not allowed.

## How to fix it

Use a valid array key type such as `int` or `string`:

```diff-php
 <?php declare(strict_types = 1);

-$key = new stdClass();
-$array = [$key => 'value'];
+$key = 'my_key';
+$array = [$key => 'value'];
```
