---
title: "impure.functionCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function computeAndLog(int $value): int
{
	file_put_contents('/tmp/log.txt', (string) $value);

	return $value * 2;
}
```

## Why is it reported?

An impure function is called inside a function or method marked as `@phpstan-pure`. Pure functions must not have side effects -- they should only compute and return a value based on their inputs. Calling an impure function (one that performs I/O, modifies global state, etc.) violates this contract.

In the example above, `file_put_contents()` writes to a file, which is a side effect, making the function impure despite the `@phpstan-pure` annotation.

## How to fix it

Remove the impure function call from the pure function:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-pure */
 function compute(int $value): int
 {
-	file_put_contents('/tmp/log.txt', (string) $value);
-
 	return $value * 2;
 }
```

Or remove the purity annotation if the function genuinely needs to perform side effects:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
 function computeAndLog(int $value): int
 {
 	file_put_contents('/tmp/log.txt', (string) $value);

 	return $value * 2;
 }
```
