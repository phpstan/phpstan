---
title: "impure.yield"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 * @return \Generator<int, string, void, void>
 */
function generateItems(): \Generator
{
	yield 'a'; // ERROR: Impure yield in pure function generateItems().
	yield 'b';
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Using `yield` inside a pure function is considered an impure operation because generators maintain internal state across multiple calls. Each time the generator is resumed, it can produce different values and interact with the caller through the `send()` method, making the behavior inherently stateful and impure.

## How to fix it

Remove the `@phpstan-pure` annotation if the function needs to use `yield`:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- * @return \Generator<int, string, void, void>
- */
+/**
+ * @return \Generator<int, string, void, void>
+ */
 function generateItems(): \Generator
 {
 	yield 'a';
 	yield 'b';
 }
```

Alternatively, if the function should remain pure, return an array instead of using a generator:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
- * @return \Generator<int, string, void, void>
+ * @return list<string>
  */
-function generateItems(): \Generator
+function generateItems(): array
 {
-	yield 'a';
-	yield 'b';
+	return ['a', 'b'];
 }
```
