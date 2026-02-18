---
title: "throws.void"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class MyException extends \Exception
{
}

/**
 * @throws void
 */
function doFoo(): void
{
	throw new MyException();
}
```

## Why is it reported?

The function or method has a `@throws void` PHPDoc tag, which declares that it does not throw any exceptions. However, PHPStan detected an explicit `throw` statement (or a call to a function that is known to throw) inside the function body. This is a contradiction -- the code promises not to throw but actually does.

## How to fix it

If the function can throw exceptions, update the `@throws` tag to declare the thrown type:

```diff-php
 /**
- * @throws void
+ * @throws MyException
  */
 function doFoo(): void
 {
 	throw new MyException();
 }
```

If the function should not throw, remove the `throw` statement:

```diff-php
 /**
  * @throws void
  */
 function doFoo(): void
 {
-	throw new MyException();
+	// handle the error differently
 }
```

If the `@throws void` tag was added by mistake, remove it:

```diff-php
-/**
- * @throws void
- */
 function doFoo(): void
 {
 	throw new MyException();
 }
```
