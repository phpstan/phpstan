---
title: "method.resultUnused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Formatter
{
	/** @phpstan-pure */
	public function format(string $value): string
	{
		return strtoupper($value);
	}
}

function doFoo(Formatter $formatter): void
{
	$formatter->format('hello'); // ERROR: Call to method Formatter::format() on a separate line has no effect.
}
```

## Why is it reported?

A method call appears as a standalone statement but the method has no side effects. The return value is not assigned, returned, or used in any way. Since the method is pure (or has no impure points), calling it without using its result has no observable effect and is likely a mistake.

PHPStan detects methods marked as `@phpstan-pure`, as well as methods that have no impure points (no writes to properties, no I/O, no calls to impure functions).

## How to fix it

Use the return value of the method call:

```diff-php
 <?php declare(strict_types = 1);

 function doFoo(Formatter $formatter): void
 {
-	$formatter->format('hello');
+	$result = $formatter->format('hello');
 }
```

Or if the method should have a side effect, update its implementation so PHPStan recognises it as impure:

```diff-php
 <?php declare(strict_types = 1);

 class Formatter
 {
-	/** @phpstan-pure */
-	public function format(string $value): string
+	/** @phpstan-impure */
+	public function format(string $value): string
 	{
+		echo strtoupper($value);
 		return strtoupper($value);
 	}
 }
```
