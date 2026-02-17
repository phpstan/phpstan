---
title: "parameter.variadic"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(int $i, string $j): void
	{
	}
}

class Bar extends Foo
{
	public function doFoo(int ...$i): void
	{
	}
}
```

## Why is it reported?

The overriding method declares a parameter as variadic (`...`), but the corresponding parameter in the parent method is not variadic. In the example above, `Bar::doFoo()` declares `...$i` as variadic, while `Foo::doFoo()` declares `$i` as a regular parameter.

This changes the method signature in a way that is incompatible with the parent. A variadic parameter accepts zero or more arguments, while the parent method expects a fixed number of arguments at specific positions.

## How to fix it

Match the parent method's parameter list instead of using a variadic parameter:

```diff-php
 class Bar extends Foo
 {
-	public function doFoo(int ...$i): void
+	public function doFoo(int $i, string $j): void
 	{
 	}
 }
```
