---
title: "mixin.deprecatedInterface"
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
 * @mixin OldInterface
 */
class Foo // ERROR: Class references deprecated interface OldInterface in @mixin tag.
{
}
```

## Why is it reported?

This error is reported by `phpstan/phpstan-deprecation-rules`.

The `@mixin` PHPDoc tag references an interface that has been marked as deprecated with the `@deprecated` PHPDoc tag. Using deprecated interfaces should be avoided because they may be removed in a future version of the library or application. The deprecation notice typically suggests a replacement interface.

## How to fix it

Replace the deprecated interface with its suggested replacement:

```diff-php
 <?php declare(strict_types = 1);

 /**
- * @mixin OldInterface
+ * @mixin NewInterface
  */
 class Foo
 {
 }
```
