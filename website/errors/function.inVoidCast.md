---
title: "function.inVoidCast"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): string
{
	return 'hello';
}

(void) doFoo();
```

## Why is it reported?

A function call is wrapped in a `(void)` cast, but the function does not require its return value to be used. The `(void)` cast is a way to explicitly acknowledge that a return value is being discarded, but it is only necessary for functions marked with `#[\NoDiscard]`. For other functions, the cast is redundant.

## How to fix it

Remove the unnecessary `(void)` cast:

```diff-php
 <?php declare(strict_types = 1);

-(void) doFoo();
+doFoo();
```
