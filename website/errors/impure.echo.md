---
title: "impure.echo"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-pure */
function format(string $name): string
{
	echo "Formatting: $name";
	return strtoupper($name);
}
```

## Why is it reported?

A function marked as `@phpstan-pure` contains an `echo` statement. Pure functions must not have side effects, and `echo` produces output, which is a side effect.

## How to fix it

Remove the `echo` statement from the pure function:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-pure */
 function format(string $name): string
 {
-	echo "Formatting: $name";
 	return strtoupper($name);
 }
```

Or remove the `@phpstan-pure` annotation if the function intentionally produces output.
