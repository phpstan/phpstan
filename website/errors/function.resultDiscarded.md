---
title: "function.resultDiscarded"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

#[\NoDiscard]
function computeHash(string $input): string
{
	return hash('sha256', $input);
}

computeHash('test');
```

## Why is it reported?

The function is marked with the `#[\NoDiscard]` attribute, indicating that its return value should always be used. Calling such a function on a separate line without using the return value means the computation is wasted and likely indicates a programming error.

In the example above, `computeHash()` returns a hash string, but the result is not assigned to a variable or used in any way.

## How to fix it

Use the return value of the function call:

```diff-php
 <?php declare(strict_types = 1);

-computeHash('test');
+$hash = computeHash('test');
```

If the return value is intentionally not needed, use a `(void)` cast to explicitly discard it:

```diff-php
 <?php declare(strict_types = 1);

-(computeHash('test'));
+(void) (computeHash('test'));
```
