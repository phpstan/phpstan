---
title: "nullCoalesce.expr"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	echo rand() ?? 0; // ERROR: Expression on left side of ?? is not nullable.
}
```

## Why is it reported?

The `??` (null coalescing) operator provides a fallback value when the left side is `null`. PHPStan has determined that the expression on the left side of `??` can never be `null`, so the fallback value on the right side will never be used. This makes the `??` operator redundant.

In the example above, `rand()` always returns `int`, which is never `null`.

## How to fix it

Remove the null coalescing operator since the expression is never `null`:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(): void
 {
-	echo rand() ?? 0;
+	echo rand();
 }
```

If the expression genuinely should be nullable, update the return type of the function or method being called:

```diff-php
 <?php declare(strict_types = 1);

-function getValue(): int
+function getValue(): ?int
 {
-	return 42;
+	return rand(0, 1) === 0 ? null : 42;
 }

 echo getValue() ?? 0;
```
