---
title: "return.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function doFoo(): void
{
	return 'hello';
}
```

## Why is it reported?

A function or method with a `void` return type declaration attempts to return a value. In PHP, functions declared with `void` must not return any value. A bare `return;` statement is allowed, but `return $value;` is not.

This is a violation of PHP's type system and will cause a fatal error at runtime in strict mode.

## How to fix it

Either remove the returned value:

```diff-php
 function doFoo(): void
 {
-	return 'hello';
+	// perform side effects only
 }
```

Or change the return type to match the returned value:

```diff-php
-function doFoo(): void
+function doFoo(): string
 {
 	return 'hello';
 }
```
