---
title: "possiblyImpure.print"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{

	/** @phpstan-pure */
	public function format(string $message): string
	{
		print $message;

		return '[LOG] ' . $message;
	}

}
```

## Why is it reported?

The function or method is marked as pure (via `@phpstan-pure`), but it uses `print` or `echo` inside its body. Outputting text is a side effect because it sends data to the output buffer, which modifies external state. This makes the function possibly impure.

A pure function must have no side effects and must return a result based only on its arguments.

## How to fix it

Remove the `print`/`echo` statement from the pure function:

```diff-php
 <?php declare(strict_types = 1);

 class Logger
 {

 	/** @phpstan-pure */
 	public function format(string $message): string
 	{
-		print $message;
-
 		return '[LOG] ' . $message;
 	}

 }
```

Alternatively, if the function genuinely needs to produce output, remove the `@phpstan-pure` annotation:

```diff-php
 <?php declare(strict_types = 1);

 class Logger
 {

-	/** @phpstan-pure */
 	public function format(string $message): string
 	{
 		print $message;

 		return '[LOG] ' . $message;
 	}

 }
```
