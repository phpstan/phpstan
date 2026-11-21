---
title: "method.parentMethodFinal"
shortDescription: "Overriding a method that is declared as final in the parent class."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	final public function doSomething(): void
	{
		echo 'base';
	}
}

class Child extends Base
{
	public function doSomething(): void // ERROR: Method Child::doSomething() overrides final method Base::doSomething().
	{
		echo 'child';
	}
}
```

## Why is it reported?

The method being defined overrides a method that is declared as `final` in the parent class. The `final` keyword in PHP prevents child classes from overriding a method. This is a language-level constraint that will cause a fatal error at runtime.

A method is marked as `final` when the parent class author wants to guarantee that the method's behavior cannot be changed by subclasses. This is often done for security, correctness, or design reasons.

The same applies to property hooks (PHP 8.4+). A `get` or `set` hook declared as `final` cannot be overridden by a hook in a child class:

```php
<?php declare(strict_types = 1);

class Base
{
	public int $prop {
		final get => 1;
	}
}

class Child extends Base
{
	public int $prop {
		get => 2; // ERROR: Get hook for property Child::$prop overrides final get hook for property Base::$prop.
	}
}
```

## How to fix it

Do not override the final method. If different behavior is needed, consider using composition instead of inheritance:

```diff-php
 <?php declare(strict_types = 1);

-class Child extends Base
+class Child
 {
-	public function doSomething(): void
+	public function __construct(private Base $base)
+	{
+	}
+
+	public function doSomethingDifferent(): void
 	{
 		echo 'child';
 	}
+
+	public function doSomething(): void
+	{
+		$this->base->doSomething();
+	}
 }
```

Or define a separate method with a different name that provides the additional behavior:

```diff-php
 <?php declare(strict_types = 1);

 class Child extends Base
 {
-	public function doSomething(): void
+	public function doSomethingExtra(): void
 	{
+		$this->doSomething();
 		echo 'child';
 	}
 }
```
