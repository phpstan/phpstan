---
title: "callable.notSupported"
shortDescription: "Creating a callable using syntax or a target that is not supported by PHP."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
		$callable = $this->doFoo(...);
	}
}
```

## Why is it reported?

The code creates a callable in a way that PHP does not allow, or that is not available on the PHP version PHPStan is configured to analyse for. This error is reported in these situations:

- **First-class callable syntax on PHP < 8.1.** The `foo(...)` syntax was introduced in PHP 8.1. On older versions it is a syntax error.
- **Partial function application on PHP < 8.6.** Calls with `?` placeholders such as `foo('a', ?)` or `$this->doFoo(?)` create a partially applied callable. This syntax is available only on PHP 8.6 and later.
- **Combining the nullsafe operator with closure creation.** `$foo?->doFoo(...)` is a compile-time error in PHP.
- **Creating a callable from the `new` operator.** `new Foo(...)` is not supported in any PHP version.
- **Creating a callable from a function that cannot be called dynamically.** `compact()`, `extract()`, `func_get_arg()`, `func_get_args()`, `func_num_args()` and `get_defined_vars()` operate on the calling scope, so PHP refuses to call them indirectly. A first-class callable of them, such as `compact(...)`, throws `Cannot call compact() dynamically` every time it is called, and a partial application, such as `compact('a', ?)`, throws already when it is created.

```php
<?php declare(strict_types = 1);

function doFoo(?Foo $foo): void
{
	$a = compact(...); // Cannot create callable from compact(), it cannot be called dynamically.
	$b = $foo?->doBar(...); // Cannot combine nullsafe operator with Closure creation.
}
```

## How to fix it

On PHP versions older than 8.1, use `Closure::fromCallable()` or a closure wrapper instead of the first-class callable syntax:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	public function doFoo(): void
 	{
-		$callable = $this->doFoo(...);
+		$callable = Closure::fromCallable([$this, 'doFoo']);
 	}
 }
```

On PHP versions older than 8.6, replace partial function application with an arrow function:

```diff-php
-$callable = str_replace('a', 'b', ?);
+$callable = fn (string $subject): string => str_replace('a', 'b', $subject);
```

If the project already requires a newer PHP version, configure PHPStan to analyse the code for it by setting the [`phpVersion`](/config-reference#phpversion) option in the configuration file.

When combining the nullsafe operator with closure creation, handle the `null` case explicitly:

```diff-php
-$callable = $foo?->doBar(...);
+$callable = $foo !== null ? $foo->doBar(...) : null;
```

When creating a callable from the `new` operator, wrap the instantiation in a closure:

```diff-php
-$factory = new Foo(...);
+$factory = static fn (...$args): Foo => new Foo(...$args);
```

For functions that cannot be called dynamically, call them directly in the scope whose variables or arguments they should work with:

```diff-php
-$fn = compact(...);
-$vars = $fn('a', 'b');
+$vars = compact('a', 'b');
```
