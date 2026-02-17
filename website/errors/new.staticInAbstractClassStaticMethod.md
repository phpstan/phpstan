---
title: "new.staticInAbstractClassStaticMethod"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Creator
{
	public static function create(): static
	{
		return new static();
	}
}
```

## Why is it reported?

Using `new static()` inside a static method of an abstract class is unsafe because the static method can be called directly on the abstract class itself, e.g. `Creator::create()`. Since abstract classes cannot be instantiated, this would result in a fatal error at runtime.

Unlike instance methods which require an already-instantiated object, static methods can be called without an instance, making `AbstractClass::staticMethod()` a valid call site that would crash.

## How to fix it

Make the static method abstract so that each concrete subclass must provide its own implementation:

```diff-php
 <?php declare(strict_types = 1);

 abstract class Creator
 {
-	public static function create(): static
-	{
-		return new static();
-	}
+	abstract public static function create(): static;
 }
```

Alternatively, make the class final so it can be safely instantiated:

```diff-php
 <?php declare(strict_types = 1);

-abstract class Creator
+final class Creator
 {
 	public static function create(): static
 	{
 		return new static();
 	}
 }
```
