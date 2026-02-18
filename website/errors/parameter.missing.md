---
title: "parameter.missing"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	public function doFoo(string $name, int $age): void
	{
	}
}

class Child extends Base
{
	public function doFoo(string $name): void
	{
	}
}
```

## Why is it reported?

An overriding method is missing a parameter that exists in the parent method. When a child class overrides a method, it must accept at least the same parameters as the parent method. Removing a parameter from an overriding method violates the [Liskov Substitution Principle](https://en.wikipedia.org/wiki/Liskov_substitution_principle) -- code that calls the parent method with all its parameters would break when given an instance of the child class.

## How to fix it

Add the missing parameter to the overriding method:

```diff-php
 class Child extends Base
 {
-	public function doFoo(string $name): void
+	public function doFoo(string $name, int $age): void
 	{
 	}
 }
```
