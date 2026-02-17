---
title: "argument.nonUnpackAfterUnpacked"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(int $a, int $b, int $c): void {}

$args = [1, 2];
foo(...$args, 3);
```

## Why is it reported?

PHP does not allow a non-unpacked argument to follow an unpacked (`...`) argument in a function or method call. Once you use the spread operator to unpack arguments, all subsequent arguments must also be unpacked. Mixing unpacked and regular arguments in this order creates ambiguity in how parameters are mapped.

This is a compile-level restriction in PHP and will result in a fatal error at runtime.

## How to fix it

Include all arguments in the unpacked array:

```diff-php
 <?php declare(strict_types = 1);

 function foo(int $a, int $b, int $c): void {}

-$args = [1, 2];
-foo(...$args, 3);
+$args = [1, 2, 3];
+foo(...$args);
```

Or pass all arguments individually without unpacking:

```diff-php
 <?php declare(strict_types = 1);

 function foo(int $a, int $b, int $c): void {}

-$args = [1, 2];
-foo(...$args, 3);
+foo(1, 2, 3);
```
