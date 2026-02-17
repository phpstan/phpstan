---
title: "callable.resultDiscarded"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @param callable(): int $callback
 * @phpstan-pure
 */
function execute(callable $callback): int
{
    return $callback();
}

/** @var callable(): int $fn */
$fn = static function (): int { return 1; };

$fn();
```

## Why is it reported?

The callable is invoked on a separate line and its return value is discarded. The callable has been determined to require its return value to be used (for example, because it is pure and its only purpose is to produce a return value). Calling it without using the result means the call has no meaningful effect.

## How to fix it

Use the return value of the callable:

```diff-php
 <?php declare(strict_types = 1);

 /** @var callable(): int $fn */
 $fn = static function (): int { return 1; };

-$fn();
+$result = $fn();
```

If the return value is intentionally not needed, use a `(void)` cast to signal the intent:

```diff-php
 <?php declare(strict_types = 1);

 /** @var callable(): int $fn */
 $fn = static function (): int { return 1; };

-$fn();
+(void) $fn();
```
