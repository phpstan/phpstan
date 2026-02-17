---
title: "classConstant.finalNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
    final public const BAR = 'baz'; // reported
}
```

## Why is it reported?

The `final` modifier for class constants was introduced in PHP 8.1. Using it with an earlier PHP version results in a syntax error. PHPStan reports this when the configured PHP version does not support final constants.

## How to fix it

If upgrading to PHP 8.1 or later is not possible, remove the `final` modifier from the constant declaration.

```diff-php
 class Foo
 {
-    final public const BAR = 'baz';
+    public const BAR = 'baz';
 }
```
