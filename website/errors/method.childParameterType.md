---
title: "method.childParameterType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ParentClass
{
	public function process(string|int $value): void
	{
	}
}

class ChildClass extends ParentClass
{
	public function process(string $value): void
	{
	}
}
```

## Why is it reported?

A child class overrides a method from its parent, but the parameter type in the child is narrower than in the parent. This violates the Liskov Substitution Principle: code that works with the parent type should also work with any child type. In the example above, `ParentClass::process()` accepts `string|int`, but `ChildClass::process()` only accepts `string`, meaning code passing `int` would break.

## How to fix it

Keep the parameter type at least as wide as in the parent method:

```diff-php
 <?php declare(strict_types = 1);

 class ChildClass extends ParentClass
 {
-	public function process(string $value): void
+	public function process(string|int $value): void
 	{
 	}
 }
```
