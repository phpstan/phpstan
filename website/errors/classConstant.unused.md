---
title: "classConstant.unused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private const MAX_RETRIES = 3;

	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

A private class constant is declared but never read anywhere in the class. Since private constants cannot be accessed from outside the class, this constant is dead code. It may be leftover from a refactoring or a constant that was defined but never actually used.

In the example above, `Foo::MAX_RETRIES` is declared as `private` but is never used within the `Foo` class.

Learn more: [Always-Used Class Constants](/developing-extensions/always-used-class-constants)

## How to fix it

Remove the unused constant:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private const MAX_RETRIES = 3;
-
 	public function doSomething(): void
 	{
 	}
 }
```

Or use the constant in the class:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private const MAX_RETRIES = 3;

 	public function doSomething(): void
 	{
+		for ($i = 0; $i < self::MAX_RETRIES; $i++) {
+			// retry logic
+		}
 	}
 }
```

If the constant is used through reflection or other dynamic means not visible to static analysis, implement the [Always-Used Class Constants](/developing-extensions/always-used-class-constants) extension.
