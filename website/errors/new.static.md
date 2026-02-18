---
title: "new.static"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function create(): static
	{
		return new static();
	}
}
```

## Why is it reported?

Using `new static()` in a non-final class is unsafe because child classes may override the constructor with different parameters or requirements. When `new static()` is called, it creates an instance of the actual runtime class (which may be a subclass), but uses the parent's constructor call. If a child class changes the constructor signature, this will break.

Learn more: [Solving PHPStan error "Unsafe usage of new static()"](/blog/solving-phpstan-error-unsafe-usage-of-new-static)

## How to fix it

Mark the class as final if it is not meant to be extended:

```diff-php
-class Foo
+final class Foo
 {
 	public function create(): static
 	{
 		return new static();
 	}
 }
```

Or mark the constructor as final to prevent subclasses from changing its signature:

```diff-php
 class Foo
 {
+	final public function __construct()
+	{
+	}
+
 	public function create(): static
 	{
 		return new static();
 	}
 }
```

Or add `@phpstan-consistent-constructor` to the class to declare that all subclasses must have a compatible constructor:

```diff-php
+/** @phpstan-consistent-constructor */
 class Foo
 {
 	public function create(): static
 	{
 		return new static();
 	}
 }
```
