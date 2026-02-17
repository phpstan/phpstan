---
title: "argument.unpackAfterNamed"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function foo(int $i, int $j, int $k): void {}

$args = ['j' => 2, 'k' => 3];
foo(i: 1, ...$args);
```

## Why is it reported?

PHP does not allow an unpacked argument (`...`) to follow a named argument in a function or method call. When you use a named argument like `i: 1`, all subsequent arguments passed via the spread operator (`...`) create an ambiguity because the unpacked array might contain keys that conflict with the already-specified named arguments.

This is a compile-level restriction in PHP and will result in a fatal error at runtime.

## How to fix it

Pass all arguments either as named arguments or use unpacking without mixing the two styles:

```php
<?php declare(strict_types = 1);

function foo(int $i, int $j, int $k): void {}

// Option 1: Use only named arguments
foo(i: 1, j: 2, k: 3);

// Option 2: Use only unpacking
$args = ['i' => 1, 'j' => 2, 'k' => 3];
foo(...$args);
```
