---
title: "generator.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @return \Generator<int, int, void, void>
 */
function numbers(): \Generator
{
	yield 1;
	$value = yield 2;
}
```

## Why is it reported?

The generator declares `void` as its send type (the `TSend` template parameter of `Generator`), meaning that no value is sent back into the generator via `Generator::send()`. Using the result of a `yield` expression when the send type is `void` will always produce `null`, which indicates a logic error -- the code appears to expect a value to be sent into the generator, but the type declaration says otherwise.

## How to fix it

If the generator needs to receive values via `send()`, declare the correct send type:

```diff-php
 /**
- * @return \Generator<int, int, void, void>
+ * @return \Generator<int, int, string, void>
  */
 function numbers(): \Generator
 {
 	yield 1;
 	$value = yield 2;
 }
```

If the generator does not need to receive values, remove the unused assignment:

```diff-php
 /**
  * @return \Generator<int, int, void, void>
  */
 function numbers(): \Generator
 {
 	yield 1;
-	$value = yield 2;
+	yield 2;
 }
```
