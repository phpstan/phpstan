---
title: "method.finalPrivate"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	final private function doSomething(): void
	{
	}
}
```

## Why is it reported?

A private method is marked as `final`, which is unnecessary. The `final` keyword prevents a method from being overridden in child classes, but private methods are not visible to child classes and therefore can never be overridden. Marking a private method as `final` has no practical effect.

As of PHP 8.0, PHP itself emits a deprecation notice for this combination.

## How to fix it

Remove the `final` keyword from the private method.

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	final private function doSomething(): void
+	private function doSomething(): void
 	{
 	}
 }
```
