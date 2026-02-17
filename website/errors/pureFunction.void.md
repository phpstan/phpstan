---
title: "pureFunction.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function doNothing(): void // ERROR: Function doNothing() is marked as pure but returns void.
{
}
```

## Why is it reported?

A function marked as `@phpstan-pure` must not have side effects and must return a meaningful value. A pure function that returns `void` serves no purpose -- since it has no side effects and produces no return value, calling it has no observable effect. This is almost certainly a mistake: either the function should not be marked as pure, or it should return a value.

The exception is constructors, which are allowed to be pure and return void because their purpose is to initialize an object.

## How to fix it

If the function performs side effects (like writing to a file, modifying external state, or printing output), remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function doSomething(): void
 {
 	file_put_contents('/tmp/log.txt', 'done');
 }
```

If the function truly is pure, it should return a value:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function compute(): void
+function compute(): int
 {
+	return 42;
 }
```
