---
title: "generator.valueType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return \Generator<string, int, mixed, void>
 */
function doFoo(): \Generator
{
	yield 'foo' => 'bar';
}
```

## Why is it reported?

A `yield` or `yield from` expression provides a value whose type does not match the generator's declared value type. The function declares it returns `Generator<string, int, mixed, void>`, meaning the values yielded must be of type `int`. However, the string `'bar'` is yielded as the value, which is incompatible with `int`.

## How to fix it

Yield a value that matches the declared generator value type:

```diff-php
 /**
  * @return \Generator<string, int, mixed, void>
  */
 function doFoo(): \Generator
 {
-	yield 'foo' => 'bar';
+	yield 'foo' => 42;
 }
```

Or update the generator's PHPDoc type to match the actual yielded values:

```diff-php
 /**
- * @return \Generator<string, int, mixed, void>
+ * @return \Generator<string, string, mixed, void>
  */
 function doFoo(): \Generator
 {
 	yield 'foo' => 'bar';
 }
```
