---
title: "generator.returnType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): array
{
	yield 1;
	yield 2;
}
```

## Why is it reported?

A `yield` statement is used inside a function whose return type is not compatible with generators. When a function uses `yield`, it becomes a generator function and must have a return type compatible with `Generator`, `Iterator`, `Traversable`, or `iterable`.

In the example above, the function declares a return type of `array`, but uses `yield`, which makes it a generator.

## How to fix it

Change the return type to a generator-compatible type:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): array
+function doFoo(): \Generator
 {
 	yield 1;
 	yield 2;
 }
```
