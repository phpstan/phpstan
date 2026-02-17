---
title: "trait.unused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait Loggable
{
	public function log(string $message): void
	{
		echo $message;
	}
}
```

## Why is it reported?

The trait is declared but never used by any class in the analysed codebase. PHPStan analyses traits in the context of the classes that use them, so a trait that is not used anywhere is never analysed for errors. This means bugs inside the trait would go undetected.

See: [How PHPStan analyses traits](https://phpstan.org/blog/how-phpstan-analyses-traits)

## How to fix it

Use the trait in at least one class so that PHPStan can analyse its code, or remove the trait if it is no longer needed.

```diff-php
 <?php declare(strict_types = 1);

 trait Loggable
 {
 	public function log(string $message): void
 	{
 		echo $message;
 	}
 }
+
+class UserService
+{
+	use Loggable;
+}
```
