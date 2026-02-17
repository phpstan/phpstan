---
title: "new.privateConstructor"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Singleton
{
	private function __construct()
	{
	}

	public static function create(): self
	{
		return new self();
	}
}

new Singleton(); // ERROR: Cannot instantiate class Singleton via private constructor Singleton::__construct().
```

## Why is it reported?

A class with a `private` constructor is being instantiated from outside the class (or from a context that does not have access to the private constructor). A private constructor restricts instantiation to within the class itself, which is a common pattern for singletons, factory methods, or named constructors. Attempting to call `new` on such a class from outside will cause a fatal error at runtime.

## How to fix it

Use the provided factory method or named constructor instead of calling `new` directly:

```diff-php
 <?php declare(strict_types = 1);

-new Singleton();
+Singleton::create();
```

Or if the constructor should be accessible from outside the class, change its visibility:

```diff-php
 <?php declare(strict_types = 1);

 class Singleton
 {
-	private function __construct()
+	public function __construct()
 	{
 	}
 }
```
