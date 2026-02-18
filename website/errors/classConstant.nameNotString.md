---
title: "classConstant.nameNotString"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public const BAR = 'bar';
}

function doFoo(int $name): void
{
	echo Foo::{$name};
}
```

## Why is it reported?

When accessing a class constant dynamically with `ClassName::{$expr}` (available since PHP 8.3), the expression must evaluate to a string. If the expression has a non-string type such as `int`, `object`, or `mixed`, PHP will throw a `TypeError` at runtime. In the example above, `$name` is an `int`, which is not a valid class constant name.

## How to fix it

Ensure the dynamic constant name expression is a string:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public const BAR = 'bar';
 }

-function doFoo(int $name): void
+function doFoo(string $name): void
 {
 	echo Foo::{$name};
 }
```
