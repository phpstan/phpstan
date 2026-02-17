---
title: "assert.deprecatedClass"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewLogger instead */
class OldLogger
{
}

class Checker
{
	/**
	 * @phpstan-assert OldLogger $value
	 */
	public function assertLogger(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@phpstan-assert` PHPDoc tag references a class that has been marked as `@deprecated`. Deprecated classes are planned for removal in a future version, and assertions should not rely on them.

## How to fix it

Replace the deprecated class with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

 class Checker
 {
 	/**
-	 * @phpstan-assert OldLogger $value
+	 * @phpstan-assert NewLogger $value
 	 */
 	public function assertLogger(mixed $value): void
 	{
 		// ...
 	}
 }
```
