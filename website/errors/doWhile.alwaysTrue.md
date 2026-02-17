---
title: "doWhile.alwaysTrue"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	do {
		echo 'looping';
	} while (true);
}
```

## Why is it reported?

The condition of the `do-while` loop always evaluates to `true`, meaning the loop will never terminate through its condition. If the loop body does not contain a `break`, `return`, or `throw` statement, the loop will run indefinitely. Even when an exit point exists, using `while (true)` may indicate a logic error or unnecessarily obscure code.

PHPStan does not report this error when the loop body contains a `break` or `return` statement, as this is a common intentional pattern (infinite loop with explicit exit).

## How to fix it

Introduce a meaningful loop condition that reflects when the loop should terminate:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(): void
+function doFoo(int $max): void
 {
+	$i = 0;
 	do {
 		echo 'looping';
-	} while (true);
+		$i++;
+	} while ($i < $max);
 }
```

If the loop is intentionally infinite with an exit condition inside, add a `break` or `return` statement in the loop body so PHPStan does not report it:

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	do {
		$result = doSomething();
		if ($result === false) {
			break;
		}
	} while (true);
}
```
