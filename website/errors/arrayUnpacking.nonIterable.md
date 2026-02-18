---
title: "arrayUnpacking.nonIterable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$value = 'hello';

$array = [...$value];
```

## Why is it reported?

The spread operator (`...`) is used to unpack a value inside an array literal, but the value being unpacked is not iterable. Only arrays and `Traversable` objects can be unpacked in this context. PHP will throw a `TypeError` at runtime if a non-iterable value is unpacked.

In the example above, a `string` is being unpacked, which is not iterable.

## How to fix it

Ensure the value being unpacked is an array or iterable:

```diff-php
 <?php declare(strict_types = 1);

-$value = 'hello';
+$value = ['h', 'e', 'l', 'l', 'o'];

 $array = [...$value];
```

Or wrap it in an array if you want to include it as a single element:

```diff-php
 <?php declare(strict_types = 1);

 $value = 'hello';

-$array = [...$value];
+$array = [$value];
```
