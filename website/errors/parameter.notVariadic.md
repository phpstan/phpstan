---
title: "parameter.notVariadic"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Handler
{
	public function handle(string ...$args): void;
}

class MyHandler implements Handler
{
	public function handle(string $args): void
	{
	}
}
```

## Why is it reported?

A method parameter is declared as non-variadic, but the corresponding parameter in the parent method or interface is variadic. This violates the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle) -- code that calls the parent method with multiple arguments would fail when the overriding method is called instead.

In the example above, `Handler::handle()` accepts any number of string arguments, but `MyHandler::handle()` accepts only one.

## How to fix it

Make the parameter variadic to match the parent declaration:

```diff-php
 class MyHandler implements Handler
 {
-	public function handle(string $args): void
+	public function handle(string ...$args): void
 	{
 	}
 }
```
