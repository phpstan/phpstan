---
title: "throws.unusedType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @throws \InvalidArgumentException|\DomainException
 */
function doFoo(): void
{
	throw new \InvalidArgumentException();
}
```

## Why is it reported?

The `@throws` PHPDoc tag declares an exception type that is never actually thrown within the function or method body. In the example above, `DomainException` is listed in `@throws` but only `InvalidArgumentException` is thrown.

Declaring unused throw types makes the `@throws` documentation inaccurate. Callers may add unnecessary catch blocks for exceptions that can never occur.

For methods, this rule is controlled by the [`checkTooWideThrowTypesInProtectedAndPublicMethods`](/config-reference#checktoowidethrowtypesinprotectedandpublicmethods) configuration option.

## How to fix it

Remove the unused exception type from the `@throws` tag:

```diff-php
 /**
- * @throws \InvalidArgumentException|\DomainException
+ * @throws \InvalidArgumentException
  */
 function doFoo(): void
 {
 	throw new \InvalidArgumentException();
 }
```

If the function does not throw any exceptions at all, use `@throws void`:

```diff-php
 /**
- * @throws \DomainException
+ * @throws void
  */
 function doBar(): void
 {
 }
```
