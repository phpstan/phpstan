---
title: "possiblyImpure.functionCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function pureFunction(callable $callback): int
{
	return $callback();
}
```

## Why is it reported?

A function marked as `@phpstan-pure` calls another function or callable whose purity is unknown. The called function or callable might have side effects, which would make the calling function impure. PHPStan cannot determine if the call is actually pure, so it reports it as possibly impure.

## How to fix it

Ensure the called function is also marked as pure, or remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-pure */
-function pureFunction(callable $callback): int
+/** @phpstan-pure */
+function pureFunction(\Closure $callback): int
 {
 	return $callback();
 }
```

Or type the closure parameter with a `@phpstan-pure` callable type.
