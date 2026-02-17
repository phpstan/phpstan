---
title: "impure.new"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	public function __construct()
	{
		echo 'Logger initialized';
	}
}

/**
 * @phpstan-pure
 */
function createLogger(): Logger
{
	return new Logger(); // ERROR: Impure instantiation of class Logger in pure function createLogger().
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Instantiating a class with `new` is considered impure when the constructor has side effects (such as writing to a file, echoing output, or modifying global state). Even if the constructor appears side-effect-free, PHPStan reports this when it detects that the constructor is impure or possibly impure.

## How to fix it

If the class constructor is truly side-effect-free, mark it as `@phpstan-pure`:

```diff-php
 <?php declare(strict_types = 1);

 class Logger
 {
+	/**
+	 * @phpstan-pure
+	 */
 	public function __construct()
 	{
-		echo 'Logger initialized';
+		// no side effects
 	}
 }
```

Or remove the `@phpstan-pure` annotation from the calling function if it genuinely needs to create objects with impure constructors:

```diff-php
 <?php declare(strict_types = 1);

-/**
- * @phpstan-pure
- */
 function createLogger(): Logger
 {
 	return new Logger();
 }
```
