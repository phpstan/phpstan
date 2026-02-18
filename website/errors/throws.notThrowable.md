---
title: "throws.notThrowable"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @throws \DateTimeImmutable
 */
function doFoo(): void
{
	throw new \RuntimeException();
}
```

## Why is it reported?

The `@throws` PHPDoc tag specifies a type that is not a subtype of `Throwable`. In PHP, only instances of `Throwable` (which includes `Exception` and `Error` and their subclasses) can be thrown. Declaring a non-throwable type in `@throws` is incorrect because that type can never actually be thrown.

## How to fix it

Change the `@throws` tag to reference a type that implements `Throwable`:

```diff-php
 /**
- * @throws \DateTimeImmutable
+ * @throws \RuntimeException
  */
 function doFoo(): void
 {
 	throw new \RuntimeException();
 }
```

If the function does not throw any exceptions, use `@throws void`:

```diff-php
 /**
- * @throws \DateTimeImmutable
+ * @throws void
  */
 function doFoo(): void
 {
 }
```
