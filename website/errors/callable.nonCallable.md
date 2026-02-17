---
title: "callable.nonCallable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(int $value): void
{
	$value();
}
```

## Why is it reported?

An expression that is not callable is being invoked as a function. In the example above, `$value` is an `int`, which cannot be called as a function. Attempting to invoke a non-callable type will cause a runtime error.

## How to fix it

Ensure the variable being invoked is actually a callable type:

```diff-php
 <?php declare(strict_types = 1);

-function doFoo(int $value): void
+function doFoo(callable $value): void
 {
 	$value();
 }
```
