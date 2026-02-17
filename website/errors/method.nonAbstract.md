---
title: "method.nonAbstract"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class HelloWorld
{
	public function sayHello(): void;
}
```

## Why is it reported?

A non-abstract method in a non-abstract class is declared without a body. In PHP, only abstract methods in abstract classes or interfaces can omit the method body. A concrete method in a concrete class must have a body with an implementation, even if the body is empty.

This is a compile-time error in PHP and the code will not run.

## How to fix it

Add a method body:

```diff-php
 class HelloWorld
 {
-	public function sayHello(): void;
+	public function sayHello(): void
+	{
+		// implementation
+	}
 }
```

If the method is meant to be abstract, declare both the method and the class as abstract:

```diff-php
-class HelloWorld
+abstract class HelloWorld
 {
-	public function sayHello(): void;
+	abstract public function sayHello(): void;
 }
```
