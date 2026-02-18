---
title: "closure.useDuplicate"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

$fn = function (int $foo, string $bar, bool $baz) use ($baz): bool {
    return $baz;
};
```

## Why is it reported?

A closure's `use` clause imports a variable that has the same name as one of the closure's parameters. This is not allowed in PHP because it would be ambiguous which variable `$baz` refers to inside the closure body -- the parameter or the captured variable. PHP raises a fatal error in this situation.

## How to fix it

Remove the conflicting variable from the `use` clause:

```diff-php
 <?php declare(strict_types = 1);

-$fn = function (int $foo, string $bar, bool $baz) use ($baz): bool {
+$fn = function (int $foo, string $bar, bool $baz): bool {
     return $baz;
 };
```

Or rename the parameter to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

-$fn = function (int $foo, string $bar, bool $baz) use ($baz): bool {
-    return $baz;
+$fn = function (int $foo, string $bar, bool $flag) use ($baz): bool {
+    return $flag || $baz;
 };
```
