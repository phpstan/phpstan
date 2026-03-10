---
title: "void.pure"
shortDescription: "Void function or method has no side effects, making it useless."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function calculate(int $a, int $b): void
{
	$result = $a + $b;
}
```

## Why is it reported?

A function or method returns `void`, has no side effects, does not throw exceptions, does not modify parameters by reference, and does not have any `@phpstan-assert` tags. A void function without side effects is useless -- it performs computation but discards the result without affecting any observable state.

This is reported for standalone functions and private methods where PHPStan can determine that no side effects occur.

## How to fix it

If the function is supposed to produce a result, return it instead of discarding it:

```diff-php
 <?php declare(strict_types = 1);

-function calculate(int $a, int $b): void
+function calculate(int $a, int $b): int
 {
-	$result = $a + $b;
+	return $a + $b;
 }
```

If the function is supposed to have side effects (e.g., writing to a file or modifying external state), add the missing side effect:

```diff-php
 <?php declare(strict_types = 1);

 function calculate(int $a, int $b): void
 {
 	$result = $a + $b;
+	echo $result;
 }
```

If the function is intentionally impure but PHPStan cannot detect the side effect, mark it explicitly:

```diff-php
 <?php declare(strict_types = 1);

+/** @phpstan-impure */
 function calculate(int $a, int $b): void
 {
 	$result = $a + $b;
 }
```
