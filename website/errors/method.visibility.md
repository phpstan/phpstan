---
title: "method.visibility"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
	}
}

class Bar extends Foo
{
	private function doFoo(): void
	{
	}
}
```

## Why is it reported?

An overriding method has a more restrictive visibility than the parent method. PHP enforces that overriding methods must not reduce visibility: a `public` method cannot be overridden with `protected` or `private`, and a `protected` method cannot be overridden with `private`.

This is a requirement of the Liskov Substitution Principle and is enforced by PHP at runtime with a fatal error.

## How to fix it

Match or widen the visibility of the overriding method:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
-	private function doFoo(): void
+	public function doFoo(): void
 	{
 	}
 }
```
