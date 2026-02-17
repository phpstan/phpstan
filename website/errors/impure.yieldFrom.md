---
title: "impure.yieldFrom"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 * @return \Generator<int, string, void, void>
 */
function generateAll(): \Generator
{
	yield from ['a', 'b']; // ERROR: Impure yield from in pure function generateAll().
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Using `yield from` inside a pure function is considered an impure operation because it delegates to another generator or iterable while maintaining stateful generator behavior. Generators interact with the caller through `send()` and `throw()` methods, and `yield from` forwards these interactions to the inner generator, making the operation inherently impure.

## How to fix it

Remove the `@phpstan-pure` annotation if the function needs to use `yield from`:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- * @return \Generator<int, string, void, void>
- */
+/**
+ * @return \Generator<int, string, void, void>
+ */
 function generateAll(): \Generator
 {
 	yield from ['a', 'b'];
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
-function generateAll(): \Generator
+function generateAll(): array
 {
-	yield from ['a', 'b'];
+	return ['a', 'b'];
 }
```
