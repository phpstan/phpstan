---
title: "generator.sendType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @return \Generator<mixed, mixed, int, mixed> */
function innerGenerator(): \Generator
{
	$value = yield 'hello';
}

/** @return \Generator<mixed, mixed, string, mixed> */
function outerGenerator(): \Generator
{
	yield from innerGenerator();
}
```

## Why is it reported?

When using `yield from` to delegate to another generator, the `TSend` type of the delegated generator must be compatible with the `TSend` type of the delegating generator. The `TSend` type parameter (third in `Generator<TKey, TValue, TSend, TReturn>`) defines what type of values can be sent into the generator via `Generator::send()`.

In the example above, `innerGenerator()` expects `int` values to be sent into it, but `outerGenerator()` accepts `string` values. When `outerGenerator()` delegates to `innerGenerator()` via `yield from`, any `string` value sent to the outer generator would be forwarded to the inner generator, which expects `int`.

## How to fix it

Make the `TSend` types compatible between the delegating and delegated generators:

```diff-php
 <?php declare(strict_types = 1);

-/** @return \Generator<mixed, mixed, string, mixed> */
+/** @return \Generator<mixed, mixed, int, mixed> */
 function outerGenerator(): \Generator
 {
 	yield from innerGenerator();
 }
```
