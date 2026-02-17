---
title: "assert.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
	public function doSomething(): void;
}

class Foo
{
	/**
	 * @phpstan-assert OldInterface $value
	 */
	public function assertOldInterface(mixed $value): void
	{
		// ...
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@phpstan-assert` PHPDoc tag references an interface that has been marked as `@deprecated`. Using deprecated interfaces in type assertions ties your code to interfaces that are planned for removal, making future migration harder.

In the example above, the `@phpstan-assert OldInterface $value` tag references the deprecated `OldInterface`.

## How to fix it

Replace the deprecated interface with its recommended replacement in the `@phpstan-assert` tag:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	/**
-	 * @phpstan-assert OldInterface $value
+	 * @phpstan-assert NewInterface $value
 	 */
-	public function assertOldInterface(mixed $value): void
+	public function assertNewInterface(mixed $value): void
 	{
 		// ...
 	}
 }
```
