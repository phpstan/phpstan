---
title: "assert.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait Loggable
{
	public function log(string $message): void
	{
		// ...
	}
}

class Checker
{
	/**
	 * @phpstan-assert Loggable $value
	 */
	public function assertLoggable(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

A `@phpstan-assert` PHPDoc tag references a trait, which is not a valid type for assertions. Traits cannot be used as standalone types in PHP -- they cannot be instantiated, and `instanceof` checks against traits are not supported. Using a trait in a `@phpstan-assert` tag is meaningless because it does not represent a type that can be checked at runtime.

In the example above, `Loggable` is a trait, so `@phpstan-assert Loggable $value` is invalid.

## How to fix it

Replace the trait with an interface that declares the same contract:

```diff-php
 <?php declare(strict_types = 1);

-trait Loggable
+interface Loggable
 {
-	public function log(string $message): void
-	{
-		// ...
-	}
+	public function log(string $message): void;
 }

 class Checker
 {
 	/**
 	 * @phpstan-assert Loggable $value
 	 */
 	public function assertLoggable(mixed $value): void
 	{
 		// ...
 	}
 }
```
