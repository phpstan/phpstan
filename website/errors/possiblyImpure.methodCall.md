---
title: "possiblyImpure.methodCall"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Formatter
{
	public function format(string $value): string
	{
		return strtoupper($value);
	}
}

class Service
{
	/**
	 * @phpstan-pure
	 */
	public function process(Formatter $formatter): string
	{
		return $formatter->format('hello'); // ERROR: Possibly impure call to method Formatter::format() in pure method Service::process().
	}
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` calls another method whose purity is unknown. The called method might have side effects, which would make the calling function impure. PHPStan cannot guarantee that the method call is pure, so it reports it as possibly impure.

A pure function must not have any side effects and must always return the same result for the same inputs.

## How to fix it

Mark the called method as `@phpstan-pure` so PHPStan knows it is safe to call from a pure context:

```diff-php
 <?php declare(strict_types = 1);

 class Formatter
 {
+	/** @phpstan-pure */
 	public function format(string $value): string
 	{
 		return strtoupper($value);
 	}
 }
```

Or remove the `@phpstan-pure` annotation from the calling method if it does not need to be pure:

```diff-php
 <?php declare(strict_types = 1);

 class Service
 {
-	/**
-	 * @phpstan-pure
-	 */
 	public function process(Formatter $formatter): string
 	{
 		return $formatter->format('hello');
 	}
 }
```
