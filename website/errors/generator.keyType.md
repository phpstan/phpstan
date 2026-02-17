---
title: "generator.keyType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @return \Generator<string, int> */
function generateItems(): \Generator
{
	yield 1 => 'foo';
}
```

## Why is it reported?

The key type yielded by the generator does not match the key type declared in the `@return` PHPDoc tag. The `Generator` generic type is `Generator<TKey, TValue, TSend, TReturn>`, where the first type parameter specifies the expected key type.

In the example above, the function declares it returns `Generator<string, int>` (string keys, int values), but `yield 1 => 'foo'` yields an integer key `1` instead of a string key.

## How to fix it

Either fix the yielded key to match the declared type:

```diff-php
 <?php declare(strict_types = 1);

 /** @return \Generator<string, int> */
 function generateItems(): \Generator
 {
-	yield 1 => 'foo';
+	yield 'item' => 42;
 }
```

Or update the PHPDoc to match the actual yielded types:

```diff-php
 <?php declare(strict_types = 1);

-/** @return \Generator<string, int> */
+/** @return \Generator<int, string> */
 function generateItems(): \Generator
 {
 	yield 1 => 'foo';
 }
```
