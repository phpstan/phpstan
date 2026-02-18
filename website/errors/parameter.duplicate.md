---
title: "parameter.duplicate"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $a, string $a): void
{
}
```

## Why is it reported?

A function or method declares the same parameter name more than once. This is a programming error -- the second parameter shadows the first, making the first value inaccessible within the function body. Modern versions of PHP emit a compile error for this.

## How to fix it

Give each parameter a unique name:

```diff-php
-function doFoo(int $a, string $a): void
+function doFoo(int $a, string $b): void
 {
 }
```
