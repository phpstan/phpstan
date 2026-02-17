---
title: "return.never"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function terminate(): never
{
	return;
}
```

## Why is it reported?

The function or method has a `never` return type, which means it must not return at all. A function declared as `never` is expected to always throw an exception, call `exit()`/`die()`, or enter an infinite loop. Having a `return` statement in such a function contradicts the `never` return type declaration.

## How to fix it

Either remove the `return` statement and ensure the function never returns:

```diff-php
 <?php declare(strict_types = 1);

 function terminate(): never
 {
-	return;
+	exit(1);
 }
```

Or change the return type if the function is meant to return:

```diff-php
 <?php declare(strict_types = 1);

-function terminate(): never
+function terminate(): void
 {
 	return;
 }
```
