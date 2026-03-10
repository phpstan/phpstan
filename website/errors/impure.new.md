---
title: "impure.new"
shortDescription: "Pure function instantiates a class with an impure constructor."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Logger
{
	/** @phpstan-impure */
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
	return new Logger();
}
```

## Why is it reported?

A function or method marked as `@phpstan-pure` must not have side effects and must always return the same result for the same inputs. Instantiating a class with `new` is considered impure when the constructor is known to have side effects (such as writing to a file, echoing output, or modifying global state). PHPStan reports this when the constructor is explicitly marked as `@phpstan-impure`.

## How to fix it

If the class constructor is truly side-effect-free, mark it as `@phpstan-pure`:

```diff-php
 <?php declare(strict_types = 1);

 class Logger
 {
-	/** @phpstan-impure */
+	/** @phpstan-pure */
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
