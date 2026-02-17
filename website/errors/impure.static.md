---
title: "impure.static"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-pure
 */
function counter(): int
{
	static $count = 0; // error: Impure static variable in pure function counter().

	return ++$count;
}
```

## Why is it reported?

The function or method is marked as `@phpstan-pure`, but it uses a `static` variable. Static variables persist their value between calls, which means the function has hidden state and its return value can change across calls even with the same arguments. This violates the definition of a pure function, which must always return the same result for the same inputs and have no side effects.

## How to fix it

If the function truly needs to be pure, remove the static variable and pass the state as a parameter instead:

```diff-php
 <?php declare(strict_types = 1);

 /**
  * @phpstan-pure
  */
-function counter(): int
+function increment(int $count): int
 {
-	static $count = 0;
-
-	return ++$count;
+	return $count + 1;
 }
```

If the function genuinely requires persistent state, it is not pure. Remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function counter(): int
 {
 	static $count = 0;

 	return ++$count;
 }
```
