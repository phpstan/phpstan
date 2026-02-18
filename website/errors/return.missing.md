---
title: "return.missing"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

function getGreeting(string $name): string
{
	$greeting = 'Hello, ' . $name;
}
```

## Why is it reported?

A function or method declares a non-void return type but does not always return a value. Every code path must end with a `return` statement when the function has a return type. Falling off the end of the function without returning a value causes PHP to return `null`, which violates the declared return type.

When the function has a native return type, this error is not ignorable because PHP will throw a `TypeError` at runtime.

## How to fix it

Add the missing `return` statement:

```diff-php
 function getGreeting(string $name): string
 {
 	$greeting = 'Hello, ' . $name;
+	return $greeting;
 }
```

Or change the return type if the function is not meant to return a value:

```diff-php
-function getGreeting(string $name): string
+function getGreeting(string $name): void
 {
 	$greeting = 'Hello, ' . $name;
+	echo $greeting;
 }
```
