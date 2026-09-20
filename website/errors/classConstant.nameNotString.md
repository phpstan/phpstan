---
title: "classConstant.nameNotString"
shortDescription: "Dynamic class constant name expression is not a string."
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

When accessing a class constant dynamically with `ClassName::{$expr}` (available since PHP 8.3), the expression must be a string. Constant names are not cast to string at runtime, so even an object implementing `Stringable` is not accepted — only an actual `string` works. If the expression has a non-string type such as `int`, `object`, or `mixed`, the access is invalid. In the example above, `$name` is an `int`, which is not a valid class constant name.

This check is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

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
