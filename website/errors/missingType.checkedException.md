---
title: "missingType.checkedException"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class OrderService
{
	/** @throws \RuntimeException */
	public function placeOrder(): void
	{
		throw new \InvalidArgumentException('Invalid order data');
	}
}
```

## Why is it reported?

A function or method throws a checked exception that is not listed in its `@throws` PHPDoc tag. When [checked exceptions](https://phpstan.org/blog/bring-your-exceptions-under-control) are configured, PHPStan enforces that all thrown exception types are declared in the `@throws` tag.

In the example above, the method declares `@throws \RuntimeException` but throws `\InvalidArgumentException`, which extends `\LogicException`, not `\RuntimeException`. The thrown exception type is not covered by the declared `@throws` type.

This rule only applies when checked exceptions are configured in the [PHPStan configuration](/config-reference).

## How to fix it

Add the missing exception type to the `@throws` tag:

```diff-php
 <?php declare(strict_types = 1);

 class OrderService
 {
-	/** @throws \RuntimeException */
+	/** @throws \RuntimeException|\InvalidArgumentException */
 	public function placeOrder(): void
 	{
 		throw new \InvalidArgumentException('Invalid order data');
 	}
 }
```

Or use a common parent exception type that covers both:

```diff-php
 <?php declare(strict_types = 1);

 class OrderService
 {
-	/** @throws \RuntimeException */
+	/** @throws \Exception */
 	public function placeOrder(): void
 	{
 		throw new \InvalidArgumentException('Invalid order data');
 	}
 }
```

Or catch the exception inside the method so it does not propagate:

```diff-php
 <?php declare(strict_types = 1);

 class OrderService
 {
 	/** @throws \RuntimeException */
 	public function placeOrder(): void
 	{
+		try {
 			throw new \InvalidArgumentException('Invalid order data');
+		} catch (\InvalidArgumentException $e) {
+			throw new \RuntimeException('Order failed', 0, $e);
+		}
 	}
 }
```
