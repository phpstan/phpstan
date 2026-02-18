---
title: "method.abstractPrivate"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class HelloWorld
{
	abstract private function sayHello(): void;
}
```

## Why is it reported?

PHP does not allow private methods to be abstract. An abstract method is meant to be implemented by subclasses, but a private method cannot be accessed or overridden by subclasses. These two modifiers are contradictory, and PHP will produce a fatal error.

## How to fix it

Change the visibility to `protected` so the method can be implemented by subclasses:

```diff-php
 abstract class HelloWorld
 {
-	abstract private function sayHello(): void;
+	abstract protected function sayHello(): void;
 }
```
