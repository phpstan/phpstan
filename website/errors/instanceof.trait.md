---
title: "instanceof.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait Loggable
{
	public function log(string $message): void
	{
		echo $message;
	}
}

function doFoo(mixed $value): void
{
	if ($value instanceof Loggable) { // ERROR: Instanceof between mixed and trait Loggable will always evaluate to false.
		$value->log('hello');
	}
}
```

## Why is it reported?

PHP does not support using traits with the `instanceof` operator. Traits are a code reuse mechanism and do not define a type that can be checked at runtime. The `instanceof` check against a trait will always evaluate to `false`, regardless of the value being checked. This is a limitation of the PHP language, not just a PHPStan rule.

## How to fix it

Define an interface that declares the methods provided by the trait, and check against that interface instead:

```diff-php
 <?php declare(strict_types = 1);

+interface LoggableInterface
+{
+	public function log(string $message): void;
+}
+
 trait Loggable
 {
 	public function log(string $message): void
 	{
 		echo $message;
 	}
 }

 function doFoo(mixed $value): void
 {
-	if ($value instanceof Loggable) {
+	if ($value instanceof LoggableInterface) {
 		$value->log('hello');
 	}
 }
```

Classes using the trait should implement the interface:

```php
<?php declare(strict_types = 1);

class MyClass implements LoggableInterface
{
	use Loggable;
}
```
