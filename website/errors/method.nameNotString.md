---
title: "method.nameNotString"
shortDescription: "Dynamic method name in $obj->{$name}() is not a string."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
	}

	public function test(int $name): void
	{
		$this->$name();
	}
}
```

## Why is it reported?

When calling a method dynamically with the `$obj->{$name}()` syntax, the `$name` expression must be a string. Method names are not cast to string at runtime, so even an object implementing `Stringable` is not accepted — only an actual `string` works. In the example above, `$name` is an `int`, which cannot be used as a method name and results in an error.

This check is currently part of [Bleeding Edge](/blog/what-is-bleeding-edge).

## How to fix it

Ensure the dynamic method name expression is a string:

```diff-php
-	public function test(int $name): void
+	public function test(string $name): void
 	{
 		$this->$name();
 	}
```

If the value comes from a `Stringable` object, cast it to `string` first:

```diff-php
 	public function test(\Stringable $name): void
 	{
-		$this->$name();
+		$this->{(string) $name}();
 	}
```
