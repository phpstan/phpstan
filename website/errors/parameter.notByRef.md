---
title: "parameter.notByRef"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Processor
{
	public function process(string &$value): void;
}

class MyProcessor implements Processor
{
	public function process(string $value): void
	{
	}
}
```

## Why is it reported?

A child class or implementation declares a parameter as not passed by reference, but the parent method or interface declares the corresponding parameter as passed by reference. This is a violation of the Liskov Substitution Principle.

This can also be reported when a `@param-out` PHPDoc tag is used on a parameter that is not passed by reference.

## How to fix it

Match the parent's parameter passing convention:

```diff-php
 <?php declare(strict_types = 1);

 class MyProcessor implements Processor
 {
-	public function process(string $value): void
+	public function process(string &$value): void
 	{
 	}
 }
```
