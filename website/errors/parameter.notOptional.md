---
title: "parameter.notOptional"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(int $i, int $j = 0): void
	{
	}
}

class Bar extends Foo
{
	public function doFoo(int $i, int $j): void
	{
	}
}
```

## Why is it reported?

The overriding method makes a parameter required that is optional in the parent method. In the example above, parameter `$j` has a default value in `Foo::doFoo()` (making it optional), but the overriding method `Bar::doFoo()` declares `$j` without a default value (making it required).

This violates the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle). Code that calls `$foo->doFoo(1)` with only one argument would break when `$foo` is an instance of `Bar`, because `Bar::doFoo()` requires two arguments.

## How to fix it

Make the parameter optional in the child class to match the parent:

```diff-php
 class Bar extends Foo
 {
-	public function doFoo(int $i, int $j): void
+	public function doFoo(int $i, int $j = 0): void
 	{
 	}
 }
```
