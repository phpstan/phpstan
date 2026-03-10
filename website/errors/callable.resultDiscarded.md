---
title: "callable.resultDiscarded"
shortDescription: "Return value of a callable that requires its result to be used is discarded."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\NoDiscard]
function pureCompute(int $x): int
{
	return $x * 2;
}

$fn = Closure::fromCallable('pureCompute');
$fn(5);
```

## Why is it reported?

The callable is invoked on a separate line and its return value is discarded. The underlying function is marked with `#[\NoDiscard]` (PHP 8.5+), which indicates its return value must be used. Calling it without using the result means the call has no meaningful effect.

This error is not ignorable.

## How to fix it

Use the return value of the callable:

```diff-php
 $fn = Closure::fromCallable('pureCompute');
-$fn(5);
+$result = $fn(5);
```
