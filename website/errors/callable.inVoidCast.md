---
title: "callable.inVoidCast"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

$fn = function (): int {
    return 42;
};

(void) $fn();
```

## Why is it reported?

The `(void)` cast (PHP 8.5+) is used to explicitly discard a return value of a callable that does not require its return value to be used. The `(void)` cast is intended only for callables marked with `#[NoDiscard]` where the return value must normally be used. Using `(void)` on a callable that already allows discarding its return value is unnecessary and misleading.

## How to fix it

Remove the `(void)` cast since the callable already allows discarding the return value:

```diff-php
 <?php declare(strict_types = 1);

 $fn = function (): int {
     return 42;
 };

-(void) $fn();
+$fn();
```

Or, if the callable's return value should not be discarded, add the `#[NoDiscard]` attribute to the callable:

```diff-php
 <?php declare(strict_types = 1);

+#[\NoDiscard]
 function compute(): int {
     return 42;
 }

 // Now (void) is meaningful - it explicitly discards a must-use return value
 (void) compute();
```
