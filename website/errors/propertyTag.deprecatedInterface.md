---
title: "propertyTag.deprecatedInterface"
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

/**
 * @property OldInterface $handler
 */
class Foo
{
	// magic property using deprecated interface
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A `@property` PHPDoc tag references an interface that has been marked as `@deprecated`. Using deprecated interfaces in `@property` type declarations ties your code to interfaces that are planned for removal, making future migration harder.

## How to fix it

Replace the deprecated interface with its recommended replacement in the `@property` tag:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @property OldInterface $handler
+ * @property NewInterface $handler
  */
 class Foo
 {
 	// magic property using current interface
 }
```
