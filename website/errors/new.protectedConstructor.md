---
title: "new.protectedConstructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	protected function __construct()
	{
	}

	public static function create(): static
	{
		return new static();
	}
}

new Base();
```

## Why is it reported?

A class with a `protected` constructor is being instantiated from outside the class hierarchy. A protected constructor restricts instantiation to within the class itself and its subclasses. Attempting to call `new` on such a class from outside the class hierarchy will cause a fatal error at runtime.

## How to fix it

Use the provided factory method instead of calling `new` directly:

```diff-php
-new Base();
+Base::create();
```

Or if the constructor should be accessible from outside the class, change its visibility:

```diff-php
 class Base
 {
-	protected function __construct()
+	public function __construct()
 	{
 	}
 }
```

Or instantiate the class from within a subclass:

```diff-php
 class Child extends Base
 {
 	public static function createChild(): self
 	{
 		return new self();
 	}
 }
```
