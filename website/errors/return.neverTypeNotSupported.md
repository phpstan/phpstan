---
title: "return.neverTypeNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$fn = fn(): never => throw new \RuntimeException('error');
```

## Why is it reported?

The `never` return type in arrow functions is supported only on PHP 8.2 and later. On earlier PHP versions, this syntax causes a compile-time error.

## How to fix it

Upgrade to PHP 8.2 or later.

Or use a closure instead of an arrow function:

```diff-php
-$fn = fn(): never => throw new \RuntimeException('error');
+$fn = function (): never {
+	throw new \RuntimeException('error');
+};
```

Or remove the `never` return type and use a PHPDoc annotation:

```diff-php
+/** @return never */
-$fn = fn(): never => throw new \RuntimeException('error');
+$fn = fn() => throw new \RuntimeException('error');
```
