---
title: "argument.unresolvableType"
shortDescription: "Argument type becomes unresolvable after generic template type substitution."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	/**
	 * @template T
	 * @param T $p
	 * @param value-of<T> $v
	 */
	public function doBar($p, $v): void
	{
	}
}

function doFoo(Foo $foo): void
{
	$foo->doBar(0, 0);
}
```

## Why is it reported?

PHPStan reports this error when calling a [generic](/blog/generics-in-php-using-phpdocs) function or method and a parameter's type becomes unresolvable after template type substitution. This happens when the template type resolves to a value that makes a dependent type meaningless.

In the example above, `doBar` declares `value-of<T>` for the second parameter `$v`. When `T` resolves to `int` (because `0` is passed as the first argument), `value-of<int>` is not a valid type construct since `int` is not an array or enum. The resolved parameter type becomes unresolvable.

## How to fix it

Pass an argument whose type makes the dependent type parameter resolvable. For `value-of<T>`, `T` should be an array or an enum type:

```diff-php
 function doFoo(Foo $foo): void
 {
-	$foo->doBar(0, 0);
+	/** @var array{a: 1, b: 2} $arr */
+	$arr = ['a' => 1, 'b' => 2];
+	$foo->doBar($arr, 1);
 }
```
