---
title: "method.abstract"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	abstract public function doFoo(): void;
}
```

## Why is it reported?

A non-abstract class contains an abstract method, or a non-abstract class does not implement all abstract methods from its parent class or interfaces. In PHP, only abstract classes can declare abstract methods. A concrete (non-abstract) class must provide implementations for all inherited abstract methods.

PHP will throw a fatal error at runtime if a non-abstract class contains an abstract method or fails to implement all abstract methods from its parent.

## How to fix it

Implement the abstract method in the class:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	abstract public function doFoo(): void;
+	public function doFoo(): void
+	{
+		// implementation
+	}
 }
```

Or declare the class as abstract:

```diff-php
 <?php declare(strict_types = 1);

-class Foo
+abstract class Foo
 {
 	abstract public function doFoo(): void;
 }
```

When the abstract method comes from a parent class or interface, implement it:

```diff-php
 <?php declare(strict_types = 1);

 interface Logger
 {
 	public function log(string $message): void;
 }

 class FileLogger implements Logger
 {
+	public function log(string $message): void
+	{
+		file_put_contents('log.txt', $message, FILE_APPEND);
+	}
 }
```
