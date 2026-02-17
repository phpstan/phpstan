---
title: "phpstanPlayground.never"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

function bail(): void
{
	throw new \RuntimeException('Something went wrong');
}
```

## Why is it reported?

The function or method always throws an exception or terminates script execution (e.g. via `exit` or `die`), but its return type is not declared as `never`. The `never` return type was introduced in PHP 8.1 and signals that the function will never return normally.

This information helps PHPStan understand that code after a call to this function is unreachable, enabling more precise analysis.

## How to fix it

Change the return type to `never`:

```diff-php
 <?php declare(strict_types = 1);

-function bail(): void
+function bail(): never
 {
 	throw new \RuntimeException('Something went wrong');
 }
```

If you need to support PHP versions before 8.1, use the `@return never` PHPDoc tag:

```diff-php
 <?php declare(strict_types = 1);

+/** @return never */
 function bail(): void
 {
 	throw new \RuntimeException('Something went wrong');
 }
```
