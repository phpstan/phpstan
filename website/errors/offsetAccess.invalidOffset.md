---
title: "offsetAccess.invalidOffset"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$a = [];

$foo = $a[new DateTimeImmutable()];
$a[[]] = $foo;
```

## Why is it reported?

The value used as an array key is not a valid array key type. PHP arrays only accept `int` and `string` as keys. Using objects, arrays, or other types as array keys results in a runtime error.

Types that are implicitly converted to `int` or `string` (like `bool`, `float`, or `null`) may also be reported depending on the PHP version and PHPStan configuration, because these implicit conversions are often unintentional and can lead to subtle bugs.

## How to fix it

Convert the value to a valid array key type:

```diff-php
-$foo = $a[new DateTimeImmutable()];
+$foo = $a[$dateTime->format('Y-m-d')];
```

```diff-php
-$a[[]] = $foo;
+$a[implode(',', $keys)] = $foo;
```

If you need to use objects as keys, use `SplObjectStorage` or `WeakMap`:

```diff-php
-$a = [];
-$a[new DateTimeImmutable()] = 'value';
+$a = new SplObjectStorage();
+$a[new DateTimeImmutable()] = 'value';
```
