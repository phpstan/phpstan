---
title: "impureFunction.pure"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	/** @phpstan-impure */
	public function log(string $message): void
	{
		file_put_contents('/var/log/app.log', $message);
	}
}

class Calculator
{
	/** @phpstan-impure */
	public function add(int $a, int $b): int
	{
		return $a + $b;
	}
}
```

## Why is it reported?

A function or method is marked as impure (via the `@phpstan-impure` PHPDoc tag) but does not contain any actual side effects. PHPStan detects that the function has no impure points such as I/O operations, property assignments, global state access, or calls to other impure functions. If a function truly has no side effects, it should not be marked as impure.

## How to fix it

If the function genuinely has no side effects, remove the `@phpstan-impure` annotation. Optionally mark it as `@phpstan-pure` instead.

```diff-php
 <?php declare(strict_types = 1);

 class Calculator
 {
-	/** @phpstan-impure */
+	/** @phpstan-pure */
 	public function add(int $a, int $b): int
 	{
 		return $a + $b;
 	}
 }
```
